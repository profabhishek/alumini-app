<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupMember;
use App\Models\GroupInviteLink;
use App\Models\GroupInvitation;
use App\Models\AlumniUser;
use App\Mail\GroupInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\AlumniNotification;
use App\Services\NotificationHelper;

class GroupController extends Controller
{
    // ── Directory ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $myId = (int) session('alumni_id');

        // Mark all groups as read when landing on the groups index page
        // This clears the sidebar badge immediately on page load and after refresh
        CommunityGroupMember::where('alumni_id', $myId)
            ->where('status', 'approved')
            ->update(['last_read_at' => now()]);

        // Mark group-related social notifications as read
        AlumniNotification::where('recipient_id', $myId)
            ->whereIn('type', ['group_join', 'group_post_pending', 'group_member_joined', 'group_new_post'])
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $query = CommunityGroup::where('status', 'active')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $groups = $query->paginate(12)->withQueryString();

        return view('community.groups.index', [
            'groups' => $groups,
            'search' => $request->input('search', ''),
            'myId'   => $myId,
        ]);
    }

    public function pendingEdits(CommunityGroup $group)
    {
        $this->authorizeGroupManagement($group);

        $myId        = (int) session('alumni_id');
        $myRole      = session('alumni_role');
        $isSiteAdmin = in_array($myRole, ['admin', 'super_admin']);

        // New posts awaiting first approval
        $pendingPosts = \App\Models\Post::where('group_id', $group->id)
            ->where('status', 'pending_review')
            ->with('author')
            ->oldest()
            ->get();

        // Published posts with a pending edit
        $pendingEdits = \App\Models\Post::where('group_id', $group->id)
            ->where('status', 'active')
            ->whereNotNull('pending_body')
            ->with('author')
            ->oldest()
            ->get();

        return view('community.groups.pending-edits', compact(
            'group', 'pendingPosts', 'pendingEdits', 'myId', 'isSiteAdmin'
        ));
    }

    // ── Create ───────────────────────────────────────────────────────────

    public function create()
    {
        return view('community.groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'group';
        $slug = $baseSlug;
        $i = 1;
        while (CommunityGroup::where('slug', $slug)->exists()) {
            $i++;
            $slug = $baseSlug . '-' . $i;
        }

        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('groups', 'public')
            : null;

        $group = DB::transaction(function () use ($validated, $slug, $coverPath) {
            $group = CommunityGroup::create([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'description' => $validated['description'] ?? null,
                'cover_image' => $coverPath,
                'created_by'  => session('alumni_id'),
                'status'      => 'active',
            ]);

            // Creator becomes the group's first admin, auto-approved.
            CommunityGroupMember::create([
                'group_id'  => $group->id,
                'alumni_id' => session('alumni_id'),
                'role'      => 'admin',
                'status'    => 'approved',
                'joined_at' => now(),
            ]);

            return $group;
        });

        return redirect()->route('groups.show', $group->slug)
            ->with('success', "Group created! You're the group admin.");
    }

    // ── Show ─────────────────────────────────────────────────────────────

    public function show(CommunityGroup $group)
    {
        abort_if($group->status !== 'active', 404);

        $myId   = (int) session('alumni_id');
        $myRole = session('alumni_role');

        $membership = $group->membership($myId);
        $isApproved = $membership && $membership->isApproved();
        $isPending  = $membership && $membership->isPending();
        $groupRole  = $isApproved ? $membership->role : null;

        $isGroupAdmin = $groupRole === 'admin';
        $isGroupMod   = in_array($groupRole, ['admin', 'moderator']);
        $isSiteAdmin  = in_array($myRole, ['admin', 'super_admin']);

        $pendingCount = ($isGroupMod || $isSiteAdmin)
            ? $group->pendingMembers()->count()
            : 0;

        $pendingEditsCount = ($isGroupMod || $isSiteAdmin)
            ? Post::where('group_id', $group->id)
                ->where(function ($q) {
                    $q->whereNotNull('pending_body')
                    ->orWhere('status', 'pending_review');
                })
                ->count()
            : 0;

        // Only the original creator (or super_admin) may delete the group
        $isCreator = (int) $group->created_by === $myId || $myRole === 'super_admin';

    return view('community.groups.show', compact(
        'group', 'isApproved', 'isPending', 'groupRole',
        'isGroupAdmin', 'isGroupMod', 'isSiteAdmin', 'pendingCount',
        'pendingEditsCount', 'isCreator'
    ));
    }

    // ── Join / Leave ─────────────────────────────────────────────────────

    public function join(CommunityGroup $group)
    {
        abort_if($group->status !== 'active', 404);

        $myId = (int) session('alumni_id');

        if ($group->isApprovedMember($myId)) {
            return back()->with('info', 'You are already a member of this group.');
        }

        if ($group->hasPendingRequest($myId)) {
            return back()->with('info', 'Your request to join is already pending approval.');
        }

        CommunityGroupMember::create([
            'group_id'  => $group->id,
            'alumni_id' => $myId,
            'role'      => 'member',
            'status'    => 'pending',
        ]);

        return back()->with('success', 'Request to join sent — an admin or moderator will review it.');
    }

    public function leave(CommunityGroup $group)
    {
        $myId = (int) session('alumni_id');
        $membership = $group->membership($myId);

        if (!$membership) {
            return back()->with('info', 'You are not a member of this group.');
        }

        if (
            $membership->role === 'admin'
            && $group->approvedMembers()->where('role', 'admin')->count() <= 1
        ) {
            return back()->with('error', "You're the only admin — promote someone else to admin first, or delete the group.");
        }

        $membership->delete();

        return redirect()->route('groups.index')->with('success', 'You left the group.');
    }

    // ── Delete group (creator only) ───────────────────────────────────────

    public function destroy(CommunityGroup $group)
    {
        $myId   = (int) session('alumni_id');
        $myRole = session('alumni_role');

        // Only the original creator or a site super_admin may delete the group
        if ((int) $group->created_by !== $myId && $myRole !== 'super_admin') {
            abort(403, 'Only the group creator can delete this group.');
        }

        $groupName = $group->name;

        DB::transaction(function () use ($group) {
            CommunityGroupMember::where('group_id', $group->id)->delete();
            GroupInviteLink::where('group_id', $group->id)->delete();
            GroupInvitation::where('group_id', $group->id)->delete();
            Post::where('group_id', $group->id)->delete();
            $group->delete();
        });

        return redirect()->route('groups.index')
            ->with('success', '"' . $groupName . '" has been permanently deleted.');
    }

    // ── Member management ────────────────────────────────────────────────

    private function authorizeGroupManagement(CommunityGroup $group): void
    {
        $myId   = (int) session('alumni_id');
        $myRole = session('alumni_role');

        abort_unless(
            $group->isGroupModerator($myId) || in_array($myRole, ['admin', 'super_admin']),
            403
        );
    }

    private function authorizeRoleManagement(CommunityGroup $group): void
    {
        $myId   = (int) session('alumni_id');
        $myRole = session('alumni_role');

        abort_unless(
            $group->isGroupAdmin($myId) || in_array($myRole, ['admin', 'super_admin']),
            403
        );
    }

    public function members(CommunityGroup $group)
    {
        $this->authorizeGroupManagement($group);

        $myId        = (int) session('alumni_id');
        $myRole      = session('alumni_role');
        $isSiteAdmin = in_array($myRole, ['admin', 'super_admin']);
        $canManageRoles = $group->isGroupAdmin($myId) || $isSiteAdmin;

        $pending = $group->pendingMembers()->with('alumni')->oldest()->get();

        $roleOrder = ['admin' => 0, 'moderator' => 1, 'member' => 2];
        $approved = $group->approvedMembers()->with('alumni')->get()
            ->sortBy(fn($m) => $roleOrder[$m->role] ?? 3)
            ->values();

        return view('community.groups.members', compact(
            'group', 'pending', 'approved', 'myId', 'isSiteAdmin', 'canManageRoles'
        ));
    }

    public function approveMember(CommunityGroup $group, CommunityGroupMember $member)
    {
        $this->authorizeGroupManagement($group);

        abort_unless((int) $member->group_id === $group->id, 404);
        abort_unless($member->status === 'pending', 422);

        $member->update(['status' => 'approved', 'joined_at' => now()]);

        return back()->with('success', $member->alumni->full_name . ' was approved.');
    }

    public function rejectMember(CommunityGroup $group, CommunityGroupMember $member)
    {
        $this->authorizeGroupManagement($group);

        abort_unless((int) $member->group_id === $group->id, 404);
        abort_unless($member->status === 'pending', 422);

        $name = $member->alumni->full_name;
        $member->delete();

        return back()->with('success', "Rejected {$name}'s request.");
    }

    public function updateMemberRole(Request $request, CommunityGroup $group, CommunityGroupMember $member)
    {
        $this->authorizeRoleManagement($group);

        abort_unless((int) $member->group_id === $group->id, 404);
        abort_unless($member->status === 'approved', 422);

        $validated = $request->validate([
            'role' => 'required|in:member,moderator,admin',
        ]);

        if (
            $member->role === 'admin'
            && $validated['role'] !== 'admin'
            && $group->approvedMembers()->where('role', 'admin')->count() <= 1
        ) {
            return back()->with('error', 'A group must have at least one admin.');
        }

        $member->update(['role' => $validated['role']]);

        return back()->with('success', $member->alumni->full_name . "'s role was updated to " . $validated['role'] . '.');
    }

    public function removeMember(CommunityGroup $group, CommunityGroupMember $member)
    {
        $this->authorizeGroupManagement($group);

        abort_unless((int) $member->group_id === $group->id, 404);
        abort_unless($member->status === 'approved', 422);

        $myId         = (int) session('alumni_id');
        $myRole       = session('alumni_role');
        $isSiteAdmin  = in_array($myRole, ['admin', 'super_admin']);
        $isGroupAdmin = $group->isGroupAdmin($myId);

        if (!$isGroupAdmin && !$isSiteAdmin && $member->role !== 'member') {
            return back()->with('error', 'Only a group admin can remove a moderator or another admin.');
        }

        if (
            $member->role === 'admin'
            && $group->approvedMembers()->where('role', 'admin')->count() <= 1
        ) {
            return back()->with('error', 'A group must have at least one admin — promote someone else first.');
        }

        $name = $member->alumni->full_name;
        $member->delete();

        return back()->with('success', "{$name} was removed from the group.");
    }

    // ── Mark group feed as read ───────────────────────────────────────────

    public function markRead(CommunityGroup $group)
    {
        $myId = (int) session('alumni_id');
        CommunityGroupMember::where('group_id', $group->id)
            ->where('alumni_id', $myId)
            ->where('status', 'approved')
            ->update(['last_read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ── Unread badge counts for all member groups ─────────────────────────

    public function unreadCounts()
    {
        $myId = (int) session('alumni_id');

        $memberships = CommunityGroupMember::where('alumni_id', $myId)
            ->where('status', 'approved')
            ->get();

        $counts = [];
        $total  = 0;

        foreach ($memberships as $m) {
            $since = $m->last_read_at ?? $m->joined_at ?? $m->created_at;
            $count = Post::where('group_id', $m->group_id)
                ->where('status', 'active')
                ->where('created_at', '>', $since)
                ->where('alumni_id', '!=', $myId)
                ->count();

            $counts[$m->group_id] = $count;
            $total += $count;
        }

        // ── Pending posts/edits for admin/mod groups (direct DB count, not
        //    notification-based so it stays accurate after the bell is opened) ──
        $pendingCounts = [];
        $pendingTotal  = 0;
        $adminGroupIds = $memberships
            ->whereIn('role', ['admin', 'moderator'])
            ->pluck('group_id')
            ->all();

        if (!empty($adminGroupIds)) {
            Post::whereIn('group_id', $adminGroupIds)
                ->where(function ($q) {
                    // New post awaiting first approval
                    $q->where('status', 'pending_review')
                    // Published post with a member-submitted edit awaiting approval
                      ->orWhereNotNull('pending_body');
                })
                ->selectRaw('group_id, COUNT(*) as cnt')
                ->groupBy('group_id')
                ->get()
                ->each(function ($r) use (&$pendingCounts, &$pendingTotal) {
                    $pendingCounts[(int) $r->group_id] = (int) $r->cnt;
                    $pendingTotal += (int) $r->cnt;
                });
        }

        $pendingInvitations = GroupInvitation::where('alumni_id', $myId)
            ->where('status', 'pending')
            ->count();

        // Other group social notifications: join events only.
        // group_new_post excluded (already in $counts above via active-post loop).
        // group_post_pending excluded (directly counted as $pendingTotal above —
        //   stays visible even after admin opens the bell).
        $groupNotifCount = AlumniNotification::where('recipient_id', $myId)
            ->whereIn('type', ['group_join', 'group_member_joined'])
            ->where('is_read', false)
            ->count();

        return response()->json([
            'counts'              => $counts,
            'pending_counts'      => $pendingCounts,   // per-group pending items (admin/mod only)
            'total'               => $total + $pendingTotal + $groupNotifCount,
            'pending_invitations' => $pendingInvitations,
        ]);
    }

    // ── One-time invite link ──────────────────────────────────────────────

    public function generateInviteLink(CommunityGroup $group)
    {
        $this->authorizeGroupManagement($group);

        $link = GroupInviteLink::generate($group->id, (int) session('alumni_id'));
        $url  = url('/groups/join/' . $link->token);

        return response()->json(['url' => $url, 'expires_at' => $link->expires_at->toDateTimeString()]);
    }

    public function acceptInviteLink(string $token)
    {
        $link = GroupInviteLink::where('token', $token)->with('group')->firstOrFail();

        if (!$link->isValid()) {
            return redirect()->route('groups.index')
                ->with('error', 'This invite link has already been used or has expired.');
        }

        $myId  = (int) session('alumni_id');
        $group = $link->group;

        abort_if($group->status !== 'active', 404);

        if ($group->isApprovedMember($myId)) {
            return redirect()->route('groups.show', $group->slug)
                ->with('info', 'You are already a member of this group.');
        }

        DB::transaction(function () use ($link, $group, $myId) {
            $link->update(['used_by' => $myId, 'used_at' => now()]);

            CommunityGroupMember::updateOrCreate(
                ['group_id' => $group->id, 'alumni_id' => $myId],
                ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]
            );
        });

        // Notify all admins/moderators of the group
        $modIds = CommunityGroupMember::where('group_id', $group->id)
            ->where('status', 'approved')
            ->whereIn('role', ['admin', 'moderator'])
            ->pluck('alumni_id');

        foreach ($modIds as $modId) {
            NotificationHelper::fire(
                recipientId: (int) $modId,
                actorId:     $myId,
                type:        'group_join',
                groupId:     $group->id,
            );
        }

        return redirect()->route('groups.show', $group->slug)
            ->with('success', 'You have joined "' . $group->name . '"!');
    }

    // ── Direct user invitations ───────────────────────────────────────────

    public function sendInvitation(Request $request, CommunityGroup $group)
    {
        $this->authorizeGroupManagement($group);

        $validated = $request->validate([
            'alumni_id' => 'required|integer|exists:alumni_users,id',
        ]);

        $targetId = (int) $validated['alumni_id'];
        $myId     = (int) session('alumni_id');

        if ($group->isApprovedMember($targetId)) {
            return response()->json(['error' => 'This user is already a member.'], 422);
        }

        $existing = GroupInvitation::where('group_id', $group->id)
            ->where('alumni_id', $targetId)->first();

        if ($existing && $existing->isPending()) {
            return response()->json(['error' => 'An invitation is already pending for this user.'], 422);
        }

        $invitation = GroupInvitation::updateOrCreate(
            ['group_id' => $group->id, 'alumni_id' => $targetId],
            ['invited_by' => $myId, 'status' => 'pending', 'responded_at' => null]
        );

        $invitation->load(['group', 'invitedBy', 'alumni']);

        // In-app notification to the invited user
        NotificationHelper::fire(
            recipientId: $targetId,
            actorId:     $myId,
            type:        'group_invitation',
            preview:     $group->name,
            groupId:     $group->id,
        );

        try {
            Mail::to($invitation->alumni->email)->queue(new GroupInvitationMail($invitation));
        } catch (\Throwable $e) {
            // log but don't fail
        }

        return response()->json(['ok' => true]);
    }

    /**
     * GET /groups/{group:slug}/search-users?q=
     * Returns alumni users NOT already a member or pending invitee of this group.
     */
    public function searchUsersForGroup(Request $request, CommunityGroup $group)
    {
        $this->authorizeGroupManagement($group);

        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['users' => []]);
        }

        // IDs to exclude: existing members
        $memberIds = CommunityGroupMember::where('group_id', $group->id)
            ->where('status', 'approved')
            ->pluck('alumni_id');

        // IDs to exclude: pending invitations
        $invitedIds = GroupInvitation::where('group_id', $group->id)
            ->where('status', 'pending')
            ->pluck('alumni_id');

        $excludeIds = $memberIds->merge($invitedIds)->unique()->all();

        $users = AlumniUser::where('is_approved', true)
            ->whereNotIn('id', $excludeIds)
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
                'initials' => strtoupper(substr($u->full_name ?? 'A', 0, 1)),
            ]);

        return response()->json(['users' => $users]);
    }

    // ── Invitations page ──────────────────────────────────────────────────

    public function myInvitations()
    {
        $myId = (int) session('alumni_id');

        $invitations = GroupInvitation::where('alumni_id', $myId)
            ->where('status', 'pending')
            ->with(['group', 'invitedBy'])
            ->latest()
            ->get();

        return view('community.groups.invitations', compact('invitations'));
    }

    public function respondInvitation(Request $request, GroupInvitation $invitation)
    {
        abort_unless((int) $invitation->alumni_id === (int) session('alumni_id'), 403);
        abort_unless($invitation->isPending(), 422);

        $validated = $request->validate(['action' => 'required|in:accept,decline']);

        if ($validated['action'] === 'accept') {
            DB::transaction(function () use ($invitation) {
                $invitation->update(['status' => 'accepted', 'responded_at' => now()]);

                CommunityGroupMember::updateOrCreate(
                    ['group_id' => $invitation->group_id, 'alumni_id' => $invitation->alumni_id],
                    ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]
                );
            });

            return response()->json([
                'ok'       => true,
                'action'   => 'accepted',
                'redirect' => route('groups.show', $invitation->group->slug),
            ]);
        }

        $invitation->update(['status' => 'declined', 'responded_at' => now()]);

        return response()->json(['ok' => true, 'action' => 'declined']);
    }
}
