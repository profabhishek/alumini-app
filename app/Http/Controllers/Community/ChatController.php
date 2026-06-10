<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;
use App\Models\ChatConversation;
use App\Models\ChatGroupJoinRequest;
use App\Models\ChatMessage;
use App\Models\ChatMessageRead;
use App\Models\ChatParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    // ── Allowed file constraints ───────────────────────────────────────────

    private const IMAGE_MAX_KB  = 10240;   // 10 MB
    private const VIDEO_MAX_KB  = 25600;   // 25 MB
    private const FILE_MAX_KB   = 15360;   // 15 MB

    private const IMAGE_MIMES   = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const VIDEO_MIMES   = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'];
    private const PDF_MIMES     = ['application/pdf'];
    private const ALLOWED_MIMES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'application/zip',
        'application/x-rar-compressed',
    ];

    // ── Page ──────────────────────────────────────────────────────────────

    public function index()
    {
        return view('community.messages.index');
    }

    // ── Conversation list ─────────────────────────────────────────────────

    /**
     * GET /chat/conversations
     * Returns conversations for the authenticated user with unread counts.
     */
    public function conversations(Request $request): JsonResponse
    {
        $myId = session('alumni_id');

        $conversations = ChatConversation::forUser($myId)
            ->with([
                'participants.alumni',
                'latestMessage.sender',
            ])
            ->orderByDesc(function ($query) {
                // Order by the latest message timestamp
                $query->select('created_at')
                      ->from('chat_messages')
                      ->whereColumn('conversation_id', 'chat_conversations.id')
                      ->whereNull('deleted_at')
                      ->orderByDesc('created_at')
                      ->limit(1);
            })
            ->get();

        $data = $conversations->map(fn($c) => $this->serializeConversation($c, $myId));

        return response()->json(['conversations' => $data]);
    }

    // ── Messages in a conversation ────────────────────────────────────────

    /**
     * GET /chat/conversations/{id}/messages?before_id=&limit=
     * Returns paginated messages (cursor-based, newest-first).
     */
    public function messages(Request $request, int $id): JsonResponse
    {
        $myId = session('alumni_id');

        $conversation = $this->findConversationForUser($id, $myId);

        $limit    = min((int) $request->input('limit', 40), 100);
        $beforeId = (int) $request->input('before_id', 0);

        $query = ChatMessage::where('conversation_id', $id)
            ->whereNull('deleted_at')
            ->with(['sender', 'replyTo.sender'])
            ->orderByDesc('id');

        if ($beforeId > 0) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->limit($limit)->get()->reverse()->values();

        // Mark as read
        if ($messages->isNotEmpty()) {
            ChatMessageRead::markRead($id, $myId, $messages->last()->id);
        }

        return response()->json([
            'messages'    => $messages->map(fn($m) => $m->toApiArray($myId)),
            'has_more'    => $messages->count() === $limit,
            'oldest_id'   => $messages->first()?->id,
        ]);
    }

    /**
     * GET /chat/conversations/{id}/poll?after_id=
     * Long-poll endpoint: returns messages newer than after_id.
     * Also returns current unread count for badge updates.
     */
    public function poll(Request $request, int $id): JsonResponse
    {
        $myId    = session('alumni_id');
        $afterId = (int) $request->input('after_id', 0);

        $conversation = $this->findConversationForUser($id, $myId);

        $messages = ChatMessage::where('conversation_id', $id)
            ->whereNull('deleted_at')
            ->where('id', '>', $afterId)
            ->with(['sender', 'replyTo.sender'])
            ->orderBy('id')
            ->limit(50)
            ->get();

        if ($messages->isNotEmpty()) {
            ChatMessageRead::markRead($id, $myId, $messages->last()->id);
        }

        return response()->json([
            'messages' => $messages->map(fn($m) => $m->toApiArray($myId)),
        ]);
    }

    /**
     * GET /chat/poll-conversations?after=
     * Lightweight poll to check if any conversation has new messages.
     * Returns only conversations with changes after a given timestamp.
     */
    public function pollConversations(Request $request): JsonResponse
    {
        $myId  = session('alumni_id');
        $after = $request->input('after'); // ISO timestamp

        $query = ChatConversation::forUser($myId)
            ->with(['participants.alumni', 'latestMessage.sender']);

        if ($after) {
            // Only return conversations where the latest message is newer
            $query->whereHas('messages', function ($q) use ($after) {
                $q->where('created_at', '>', $after);
            });
        }

        $conversations = $query->get();

        return response()->json([
            'conversations' => $conversations->map(
                fn($c) => $this->serializeConversation($c, $myId)
            ),
            'server_time' => now()->toISOString(),
        ]);
    }

    // ── Send a message ────────────────────────────────────────────────────

    /**
     * POST /chat/conversations/{id}/messages
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $myId = session('alumni_id');

        $conversation = $this->findConversationForUser($id, $myId);

        $request->validate([
            'type'        => 'required|in:text,image,video,file,pdf',
            'body'        => 'required_if:type,text|nullable|string|max:5000',
            'file'        => 'required_if:type,image,video,file,pdf|nullable|file',
            'reply_to_id' => 'nullable|integer|exists:chat_messages,id',
        ]);

        $type       = $request->input('type');
        $body       = null;
        $filePath   = null;
        $fileName   = null;
        $fileMime   = null;
        $fileSize   = null;

        if ($type === 'text') {
            $body = trim($request->input('body'));
            if (empty($body)) {
                return response()->json(['error' => 'Message cannot be empty.'], 422);
            }
        } else {
            // File upload
            $file = $request->file('file');

            if (!$file || !$file->isValid()) {
                return response()->json(['error' => 'File upload failed.'], 422);
            }

            $fileMime = $file->getMimeType();
            $fileSize = $file->getSize();

            $this->validateFileUpload($type, $fileMime, $fileSize);

            $dir      = 'chat-files/' . date('Y/m');
            $fileName = $file->getClientOriginalName();
            $safeName = Str::random(16) . '_' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME))
                        . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs($dir, $safeName, 'public');

            // Resolve actual type from MIME if needed
            if (in_array($fileMime, self::IMAGE_MIMES)) {
                $type = 'image';
            } elseif (in_array($fileMime, self::VIDEO_MIMES)) {
                $type = 'video';
            } elseif (in_array($fileMime, self::PDF_MIMES)) {
                $type = 'pdf';
            } else {
                $type = 'file';
            }
        }

        // Validate reply_to belongs to this conversation
        $replyToId = null;
        if ($request->filled('reply_to_id')) {
            $replyMsg = ChatMessage::where('id', $request->reply_to_id)
                ->where('conversation_id', $id)
                ->first();
            $replyToId = $replyMsg?->id;
        }

        $message = ChatMessage::create([
            'conversation_id' => $id,
            'sender_id'       => $myId,
            'type'            => $type,
            'body'            => $body,
            'file_path'       => $filePath,
            'file_name'       => $fileName,
            'file_mime'       => $fileMime,
            'file_size'       => $fileSize,
            'reply_to_id'     => $replyToId,
        ]);

        // Touch conversation updated_at for ordering
        $conversation->touch();

        // Auto-mark as read for the sender
        ChatMessageRead::markRead($id, $myId, $message->id);

        $message->load(['sender', 'replyTo.sender']);

        return response()->json([
            'message' => $message->toApiArray($myId),
        ], 201);
    }

    // ── Delete a message ──────────────────────────────────────────────────

    /**
     * DELETE /chat/messages/{messageId}
     */
    public function deleteMessage(int $messageId): JsonResponse
    {
        $myId    = session('alumni_id');
        $message = ChatMessage::findOrFail($messageId);

        // Only the sender can delete their own message
        if ((int) $message->sender_id !== $myId) {
            return response()->json(['error' => 'You can only delete your own messages.'], 403);
        }

        if ($message->isDeleted()) {
            return response()->json(['error' => 'Already deleted.'], 409);
        }

        // Soft-delete: mark deleted_at, don't remove the file immediately
        $message->delete();

        return response()->json(['ok' => true, 'message_id' => $messageId]);
    }

    // ── Start / find a direct conversation ───────────────────────────────

    /**
     * POST /chat/direct
     * Body: { user_id: int }
     */
    public function startDirect(Request $request): JsonResponse
    {
        $myId = session('alumni_id');

        $request->validate([
            'user_id' => 'required|integer|exists:alumni_users,id',
        ]);

        $otherId = (int) $request->input('user_id');

        if ($otherId === $myId) {
            return response()->json(['error' => 'You cannot chat with yourself.'], 422);
        }

        // Check the other user exists and is approved
        $other = AlumniUser::where('id', $otherId)
            ->where('is_approved', true)
            ->first();

        if (!$other) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Look for an existing direct conversation between these two users
        $existing = ChatConversation::where('type', 'direct')
            ->whereHas('participants', fn($q) => $q->where('alumni_id', $myId)->whereNull('left_at'))
            ->whereHas('participants', fn($q) => $q->where('alumni_id', $otherId)->whereNull('left_at'))
            ->first();

        if ($existing) {
            return response()->json([
                'conversation' => $this->serializeConversation($existing->load(['participants.alumni', 'latestMessage.sender']), $myId),
            ]);
        }

        // Create new direct conversation
        $conversation = DB::transaction(function () use ($myId, $otherId) {
            $conv = ChatConversation::create(['type' => 'direct']);

        ChatParticipant::create([
            'conversation_id' => $conv->id,
            'alumni_id' => $myId,
            'role' => 'member'
        ]);

        ChatParticipant::create([
            'conversation_id' => $conv->id,
            'alumni_id' => $otherId,
            'role' => 'member'
        ]);

            return $conv;
        });

        $conversation->load(['participants.alumni', 'latestMessage.sender']);

        return response()->json([
            'conversation' => $this->serializeConversation($conversation, $myId),
        ], 201);
    }

    // ── Groups ────────────────────────────────────────────────────────────

    /**
     * POST /chat/groups
     * Body: { name, description?, member_ids: [] }
     */
    public function createGroup(Request $request): JsonResponse
    {
        $myId = session('alumni_id');

        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'member_ids'  => 'nullable|array|max:200',
            'member_ids.*'=> 'integer|exists:alumni_users,id',
            'avatar'      => 'nullable|image|max:2048',
        ]);

        $memberIds = array_unique(array_filter(
            (array) $request->input('member_ids', []),
            fn($id) => (int)$id !== $myId
        ));

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('chat-group-avatars', 'public');
        }

        $conversation = DB::transaction(function () use ($myId, $request, $memberIds, $avatarPath) {
            $conv = ChatConversation::create([
                'type'         => 'group',
                'name'         => $request->input('name'),
                'description'  => $request->input('description'),
                'created_by'   => $myId,
                'invite_token' => Str::random(32),
                'avatar'       => $avatarPath,
            ]);

            // Creator is admin
            ChatParticipant::create([
                'conversation_id' => $conv->id,
                'alumni_id'       => $myId,
                'role'            => 'admin',
            ]);

            // Add invited members
            foreach ($memberIds as $memberId) {
                $user = AlumniUser::where('id', $memberId)->where('is_approved', true)->first();
                if ($user) {
                    ChatParticipant::create([
                        'conversation_id' => $conv->id,
                        'alumni_id'       => $memberId,
                        'role'            => 'member',
                    ]);
                }
            }

            // System message
            ChatMessage::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $myId,
                'type'            => 'system',
                'body'            => 'Group created.',
            ]);

            return $conv;
        });

        $conversation->load(['participants.alumni', 'latestMessage.sender']);

        return response()->json([
            'conversation' => $this->serializeConversation($conversation, $myId),
        ], 201);
    }

    /**
     * GET /chat/groups/{id}/info
     */
    public function groupInfo(int $id): JsonResponse
    {
        $myId = session('alumni_id');
        $conversation = $this->findConversationForUser($id, $myId);

        if (!$conversation->isGroup()) {
            return response()->json(['error' => 'Not a group.'], 422);
        }

        $conversation->load(['participants.alumni', 'creator', 'joinRequests' => fn($q) => $q->where('status', 'pending')->with('alumni')]);

        return response()->json([
            'group'    => $this->serializeGroupInfo($conversation, $myId),
        ]);
    }

    /**
     * PUT /chat/groups/{id}
     * Update group name/description/avatar. Group admins only.
     */
    public function updateGroup(Request $request, int $id): JsonResponse
    {
        $myId = session('alumni_id');
        $conversation = $this->findConversationForUser($id, $myId);

        if (!$conversation->isGroup()) {
            return response()->json(['error' => 'Not a group.'], 422);
        }

        if (!$conversation->isAdmin($myId)) {
            return response()->json(['error' => 'Only group admins can edit group settings.'], 403);
        }

        $request->validate([
            'name'        => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:500',
            'avatar'      => 'nullable|image|max:2048',
        ]);

        $updates = $request->only(['name', 'description']);

        if ($request->hasFile('avatar')) {
            if ($conversation->avatar) {
                Storage::disk('public')->delete($conversation->avatar);
            }
            $updates['avatar'] = $request->file('avatar')->store('chat-group-avatars', 'public');
        }

        $conversation->update($updates);

        return response()->json(['ok' => true, 'group' => $conversation->fresh()]);
    }

    /**
     * POST /chat/groups/{id}/members
     * Add members to a group. Admins only.
     */
    public function addMembers(Request $request, int $id): JsonResponse
    {
        $myId = session('alumni_id');
        $conversation = $this->findConversationForUser($id, $myId);

        if (!$conversation->isGroup()) {
            return response()->json(['error' => 'Not a group.'], 422);
        }
        if (!$conversation->isAdmin($myId)) {
            return response()->json(['error' => 'Only admins can add members.'], 403);
        }

        $request->validate([
            'member_ids'   => 'required|array|min:1|max:50',
            'member_ids.*' => 'integer|exists:alumni_users,id',
        ]);

        $added = 0;
        foreach ($request->input('member_ids') as $memberId) {
            $memberId = (int) $memberId;
            if ($memberId === $myId) continue;

            $user = AlumniUser::where('id', $memberId)->where('is_approved', true)->first();
            if (!$user) continue;

            // Re-add if they left, otherwise create
            $existing = ChatParticipant::where('conversation_id', $id)
                ->where('alumni_id', $memberId)
                ->first();

            if ($existing) {
                if ($existing->left_at !== null) {
                    $existing->update(['left_at' => null, 'role' => 'member']);
                    $added++;
                }
                // Already active — skip
            } else {
                ChatParticipant::create([
                    'conversation_id' => $id,
                    'alumni_id'       => $memberId,
                    'role'            => 'member',
                ]);
                $added++;
            }

            if ($added > 0) {
                $adder = AlumniUser::find($myId);
                ChatMessage::create([
                    'conversation_id' => $id,
                    'sender_id'       => $myId,
                    'type'            => 'system',
                    'body'            => "{$adder->full_name} added {$user->full_name}.",
                ]);
            }
        }

        return response()->json(['ok' => true, 'added' => $added]);
    }

    /**
     * DELETE /chat/groups/{id}/members/{memberId}
     * Remove a member. Admins only (or member removing themselves = leave).
     */
    public function removeMember(int $id, int $memberId): JsonResponse
    {
        $myId = session('alumni_id');
        $conversation = $this->findConversationForUser($id, $myId);

        if (!$conversation->isGroup()) {
            return response()->json(['error' => 'Not a group.'], 422);
        }

        $isSelf = $memberId === $myId;

        if (!$isSelf && !$conversation->isAdmin($myId)) {
            return response()->json(['error' => 'Only admins can remove members.'], 403);
        }

        $participant = ChatParticipant::where('conversation_id', $id)
            ->where('alumni_id', $memberId)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            return response()->json(['error' => 'Member not in group.'], 404);
        }

        // Prevent removing the last admin — someone must remain admin
        if ($participant->role === 'admin') {
            $adminCount = ChatParticipant::where('conversation_id', $id)
                ->where('role', 'admin')
                ->whereNull('left_at')
                ->count();

            if ($adminCount === 1) {
                return response()->json([
                    'error' => 'Cannot remove the only admin. Assign another admin first.',
                ], 422);
            }
        }

        $participant->update(['left_at' => now()]);

        $user   = AlumniUser::find($memberId);
        $actor  = AlumniUser::find($myId);
        $body   = $isSelf
            ? "{$user->full_name} left the group."
            : "{$actor->full_name} removed {$user->full_name}.";

        ChatMessage::create([
            'conversation_id' => $id,
            'sender_id'       => $myId,
            'type'            => 'system',
            'body'            => $body,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * POST /chat/groups/{id}/promote/{memberId}
     * Promote member to admin. Group admins only.
     */
    public function promoteAdmin(int $id, int $memberId): JsonResponse
    {
        $myId = session('alumni_id');
        $conversation = $this->findConversationForUser($id, $myId);

        if (!$conversation->isGroup() || !$conversation->isAdmin($myId)) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $participant = ChatParticipant::where('conversation_id', $id)
            ->where('alumni_id', $memberId)
            ->whereNull('left_at')
            ->firstOrFail();

        $participant->update(['role' => 'admin']);

        return response()->json(['ok' => true]);
    }

    // ── Invite links ──────────────────────────────────────────────────────

    /**
     * POST /chat/groups/{id}/invite/regenerate
     * Regenerate the invite link. Admins only.
     */
    public function regenerateInvite(int $id): JsonResponse
    {
        $myId = session('alumni_id');
        $conversation = $this->findConversationForUser($id, $myId);

        if (!$conversation->isGroup() || !$conversation->isAdmin($myId)) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $token = $conversation->generateInviteToken();

        return response()->json([
            'invite_url' => route('chat.join', ['token' => $token]),
            'token'      => $token,
        ]);
    }

    /**
     * GET /chat/join/{token}
     * Show "join group" confirmation page.
     */
    public function joinPage(string $token)
    {
        $conversation = ChatConversation::where('invite_token', $token)
            ->where('type', 'group')
            ->whereNull('deleted_at')
            ->firstOrFail();

        $myId = session('alumni_id');

        // Already a member?
        $isMember = $conversation->hasParticipant($myId);

        // Pending request?
        $hasPending = ChatGroupJoinRequest::where('conversation_id', $conversation->id)
            ->where('alumni_id', $myId)
            ->where('status', 'pending')
            ->exists();

        return view('community.messages.join-group', compact('conversation', 'isMember', 'hasPending', 'token'));
    }

    /**
     * POST /chat/join/{token}
     * Actually join (or request to join) a group.
     */
    public function joinGroup(string $token): JsonResponse
    {
        $myId = session('alumni_id');

        $conversation = ChatConversation::where('invite_token', $token)
            ->where('type', 'group')
            ->whereNull('deleted_at')
            ->firstOrFail();

        if ($conversation->hasParticipant($myId)) {
            return response()->json(['error' => 'You are already a member.'], 409);
        }

        // Check for rejected request — allow re-try
        ChatGroupJoinRequest::where('conversation_id', $conversation->id)
            ->where('alumni_id', $myId)
            ->where('status', 'rejected')
            ->delete();

        // Upsert join request
        $request = ChatGroupJoinRequest::firstOrCreate([
            'conversation_id' => $conversation->id,
            'alumni_id'       => $myId,
        ], ['status' => 'pending']);

        if (!$request->wasRecentlyCreated && $request->isPending()) {
            return response()->json(['message' => 'Join request already sent.']);
        }

        return response()->json(['message' => 'Join request sent. Waiting for admin approval.']);
    }

    /**
     * GET /chat/groups/{id}/join-requests
     * List pending join requests. Admins only.
     */
    public function joinRequests(int $id): JsonResponse
    {
        $myId = session('alumni_id');
        $conversation = $this->findConversationForUser($id, $myId);

        if (!$conversation->isGroup() || !$conversation->isAdmin($myId)) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $requests = ChatGroupJoinRequest::where('conversation_id', $id)
            ->where('status', 'pending')
            ->with('alumni')
            ->get()
            ->map(fn($r) => [
                'id'      => $r->id,
                'user_id' => $r->alumni_id,
                'name'    => $r->alumni->full_name,
                'avatar'  => $r->alumni->photo ? asset('storage/' . $r->alumni->photo) : null,
                'initials'=> $r->alumni->initials,
                'created_at' => $r->created_at->diffForHumans(),
            ]);

        return response()->json(['requests' => $requests]);
    }

    /**
     * PATCH /chat/groups/{id}/join-requests/{requestId}
     * Accept or reject a join request. Admins only.
     * Body: { action: 'accept' | 'reject' }
     */
    public function handleJoinRequest(Request $request, int $id, int $requestId): JsonResponse
    {
        $myId = session('alumni_id');
        $conversation = $this->findConversationForUser($id, $myId);

        if (!$conversation->isGroup() || !$conversation->isAdmin($myId)) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $request->validate(['action' => 'required|in:accept,reject']);

        $joinRequest = ChatGroupJoinRequest::where('id', $requestId)
            ->where('conversation_id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $action = $request->input('action');

        DB::transaction(function () use ($joinRequest, $action, $id, $myId, $conversation) {
            $joinRequest->update([
                'status'   => $action === 'accept' ? 'accepted' : 'rejected',
                'acted_by' => $myId,
                'acted_at' => now(),
            ]);

            if ($action === 'accept') {
                // Add to group
                $existing = ChatParticipant::where('conversation_id', $id)
                    ->where('alumni_id', $joinRequest->alumni_id)
                    ->first();

                if ($existing) {
                    $existing->update(['left_at' => null, 'role' => 'member']);
                } else {
                    ChatParticipant::create([
                        'conversation_id' => $id,
                        'alumni_id'       => $joinRequest->alumni_id,
                        'role'            => 'member',
                    ]);
                }

                $user = AlumniUser::find($joinRequest->alumni_id);
                ChatMessage::create([
                    'conversation_id' => $id,
                    'sender_id'       => $myId,
                    'type'            => 'system',
                    'body'            => "{$user->full_name} joined the group.",
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }

    // ── User search ───────────────────────────────────────────────────────

    /**
     * GET /chat/users/search?q=
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $myId = session('alumni_id');
        $q    = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['users' => []]);
        }

        $users = AlumniUser::where('id', '!=', $myId)
            ->where('is_approved', true)
            ->where(function ($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('department', 'like', "%{$q}%");
            })
            ->select('id', 'full_name', 'email', 'photo', 'department', 'current_job_title', 'current_company')
            ->limit(15)
            ->get()
            ->map(fn($u) => [
                'id'       => $u->id,
                'name'     => $u->full_name,
                'email'    => $u->email,
                'meta'     => $u->current_job_title
                               ? "{$u->current_job_title} · {$u->current_company}"
                               : $u->department,
                'avatar'   => $u->photo ? asset('storage/' . $u->photo) : null,
                'initials' => $u->initials,
            ]);

        return response()->json(['users' => $users]);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function findConversationForUser(int $conversationId, int $userId): ChatConversation
    {
        $conversation = ChatConversation::forUser($userId)
            ->findOrFail($conversationId);

        return $conversation;
    }

    private function validateFileUpload(string $type, string $mime, int $sizeBytes): void
    {
        if (!in_array($mime, self::ALLOWED_MIMES)) {
            throw ValidationException::withMessages([
                'file' => 'File type not allowed.',
            ]);
        }

        $sizeKb = $sizeBytes / 1024;

        if (in_array($mime, self::IMAGE_MIMES) && $sizeKb > self::IMAGE_MAX_KB) {
            throw ValidationException::withMessages(['file' => 'Images must be under 10 MB.']);
        }

        if (in_array($mime, self::VIDEO_MIMES) && $sizeKb > self::VIDEO_MAX_KB) {
            throw ValidationException::withMessages(['file' => 'Videos must be under 25 MB.']);
        }

        if (!in_array($mime, self::IMAGE_MIMES) && !in_array($mime, self::VIDEO_MIMES) && $sizeKb > self::FILE_MAX_KB) {
            throw ValidationException::withMessages(['file' => 'Files must be under 15 MB.']);
        }
    }

    private function serializeConversation(ChatConversation $c, int $myId): array
    {
        $latest     = $c->latestMessage;
        $unread     = $c->unreadCountFor($myId);

        if ($c->isDirect()) {
            $other = $c->otherParticipant($myId);
            $name  = $other?->full_name ?? 'Unknown User';
            $avatar = $other?->photo ? asset('storage/' . $other->photo) : null;
            $initials = $other?->initials ?? '?';
        } else {
            $name    = $c->name;
            $avatar  = $c->avatar ? asset('storage/' . $c->avatar) : null;
            $initials = strtoupper(substr($c->name ?? 'G', 0, 1));
        }

        return [
            'id'           => $c->id,
            'type'         => $c->type,
            'name'         => $name,
            'avatar'       => $avatar,
            'initials'     => $initials,
            'is_admin'     => $c->isAdmin($myId),
            'invite_token' => $c->isGroup() && $c->isAdmin($myId) ? $c->invite_token : null,
            'invite_url'   => $c->isGroup() && $c->isAdmin($myId) && $c->invite_token
                ? route('chat.join', ['token' => $c->invite_token])
                : null,
            'participant_count' => $c->participants()->count(),
            'latest_message' => $latest ? [
                'id'         => $latest->id,
                'preview'    => $latest->preview(),
                'time'       => $latest->created_at->isToday()
                    ? $latest->created_at->format('H:i')
                    : $latest->created_at->format('d M'),
                'sender_id'  => $latest->sender_id,
                'is_mine'    => (int)$latest->sender_id === $myId,
            ] : null,
            'unread_count' => $unread,
            'updated_at'   => $c->updated_at?->toISOString(),
        ];
    }

    private function serializeGroupInfo(ChatConversation $c, int $myId): array
    {
        $members = $c->participants->map(fn($p) => [
            'id'       => $p->alumni_id,
            'name'     => $p->alumni->full_name ?? 'Unknown',
            'avatar'   => $p->alumni->photo ? asset('storage/' . $p->alumni->photo) : null,
            'initials' => $p->alumni->initials ?? '?',
            'role'     => $p->role,
            'is_me'    => (int)$p->alumni_id === $myId,
        ]);

        $pendingRequests = $c->isAdmin($myId)
            ? $c->joinRequests->map(fn($r) => [
                'id'      => $r->id,
                'user_id' => $r->alumni_id,
                'name'    => $r->alumni->full_name,
                'avatar'  => $r->alumni->photo ? asset('storage/' . $r->alumni->photo) : null,
                'initials'=> $r->alumni->initials,
            ])
            : collect([]);

        return [
            'id'             => $c->id,
            'name'           => $c->name,
            'description'    => $c->description,
            'avatar'         => $c->avatar ? asset('storage/' . $c->avatar) : null,
            'invite_url'     => $c->isAdmin($myId) && $c->invite_token
                ? route('chat.join', ['token' => $c->invite_token])
                : null,
            'created_by'     => $c->created_by,
            'is_admin'       => $c->isAdmin($myId),
            'members'        => $members,
            'pending_count'  => $pendingRequests->count(),
            'join_requests'  => $pendingRequests,
        ];
    }
}