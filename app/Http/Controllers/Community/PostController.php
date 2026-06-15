<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\PostLike;
use App\Models\PostSave;
use App\Models\PostComment;
use App\Models\CommentLike;
use App\Models\AlumniUser;
use App\Models\CommunityGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    // ── Upload constraints ───────────────────────────────────────────────

    private const IMAGE_MAX_KB = 10240;  
    private const VIDEO_MAX_KB = 25600;  
    private const MAX_MEDIA    = 10;   

    private const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const VIDEO_MIMES = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'];

    // ── Community Group access helpers ──────────────────────────────────

    private function authorizeGroupAccess(CommunityGroup $group): void
    {
        $myRole = session('alumni_role');

        if (in_array($myRole, ['admin', 'super_admin'])) {
            return;
        }

        $myId = (int) session('alumni_id');

        abort_unless($group->isApprovedMember($myId), 403, 'You must be a member of this group to do that.');
    }

    private function canAccessGroupPost(?int $groupId): bool
    {
        if (!$groupId) {
            return true;
        }

        $myRole = session('alumni_role');
        if (in_array($myRole, ['admin', 'super_admin'])) {
            return true;
        }

        $myId  = (int) session('alumni_id');
        $group = CommunityGroup::find($groupId);

        return $group && $group->isApprovedMember($myId);
    }

    // ── Feed ─────────────────────────────────────────────────────────────

    public function feed(Request $request, ?CommunityGroup $group = null): JsonResponse
    {
        $myId = (int) session('alumni_id');

        if ($group) {
            $this->authorizeGroupAccess($group);
        }

        $limit    = max(1, min((int) $request->input('limit', 10), 30));
        $beforeId = (int) $request->input('before_id', 0);

        $query = Post::feed($group?->id)->whereNull('deleted_at');

        if ($beforeId > 0) {
            $query->where('id', '<', $beforeId);
        }

        $posts = $query->limit($limit)->get();

        return response()->json([
            'posts'    => $posts->map(fn($p) => $p->toFeedArray($myId)),
            'has_more' => $posts->count() === $limit,
            'oldest_id' => $posts->last()?->id,
        ]);
    }

    // ── Create post ──────────────────────────────────────────────────────

    public function store(Request $request, ?CommunityGroup $group = null): JsonResponse
    {
        $myId = (int) session('alumni_id');

        if ($group) {
            $this->authorizeGroupAccess($group);
        }

        $request->validate([
            'body'    => 'nullable|string|max:5000',
            'media'   => 'nullable|array|max:' . self::MAX_MEDIA,
            'media.*' => 'file',
        ]);

        $body  = trim((string) $request->input('body', ''));
        $files = $request->file('media', []);

        if ($body === '' && empty($files)) {
            return response()->json(['error' => 'Write something or attach a photo/video.'], 422);
        }

        $type = 'text';

        if (!empty($files)) {
            $detectedType = null;

            foreach ($files as $file) {
                if (!$file->isValid()) {
                    return response()->json(['error' => 'One of the uploaded files failed.'], 422);
                }

                $mime = $file->getMimeType();
                $size = $file->getSize();

                if (in_array($mime, self::IMAGE_MIMES)) {
                    $kind = 'image';
                } elseif (in_array($mime, self::VIDEO_MIMES)) {
                    $kind = 'video';
                } else {
                    return response()->json(['error' => 'Only images and videos are allowed.'], 422);
                }

                if ($detectedType === null) {
                    $detectedType = $kind;
                } elseif ($detectedType !== $kind) {
                    return response()->json(['error' => 'Please post either images or a video, not both.'], 422);
                }

                $sizeKb = $size / 1024;
                if ($kind === 'image' && $sizeKb > self::IMAGE_MAX_KB) {
                    return response()->json(['error' => 'Each image must be under 10 MB.'], 422);
                }
                if ($kind === 'video' && $sizeKb > self::VIDEO_MAX_KB) {
                    return response()->json(['error' => 'Each video must be under 25 MB.'], 422);
                }
                if ($kind === 'video' && count($files) > 1) {
                    return response()->json(['error' => 'Only one video can be attached per post.'], 422);
                }
            }

            $type = $detectedType;
        }

        $post = DB::transaction(function () use ($myId, $body, $type, $files, $group) {
            $post = Post::create([
                'alumni_id' => $myId,
                'body'      => $body !== '' ? $body : null,
                'type'      => $type,
                'group_id'  => $group?->id,
            ]);

            $position = 0;
            foreach ($files as $file) {
                $mime = $file->getMimeType();
                $kind = in_array($mime, self::IMAGE_MIMES) ? 'image' : 'video';

                $dir      = "post-media/{$kind}s/" . date('Y/m');
                $original = $file->getClientOriginalName();
                $safeName = Str::random(16) . '_' . Str::slug(pathinfo($original, PATHINFO_FILENAME))
                            . '.' . $file->getClientOriginalExtension();
                $path     = $file->storeAs($dir, $safeName, 'public');

                PostMedia::create([
                    'post_id'   => $post->id,
                    'type'      => $kind,
                    'file_path' => $path,
                    'file_name' => $original,
                    'file_mime' => $mime,
                    'file_size' => $file->getSize(),
                    'position'  => $position++,
                ]);
            }

            return $post;
        });

        $post->load(['author', 'media']);

        return response()->json([
            'post' => $post->toFeedArray($myId),
        ], 201);
    }

    // ── Delete post ──────────────────────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        $myId = (int) session('alumni_id');
        $post = Post::findOrFail($id);

        if (!$this->canAccessGroupPost($post->group_id)) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        $isOwner = (int) $post->alumni_id === $myId;
        $isAdmin = in_array(session('alumni_role'), ['admin', 'super_admin']);

        // Group admins/moderators can also remove posts within their group.
        $isGroupMod = $post->group_id && $post->group && $post->group->isGroupModerator($myId);

        if (!$isOwner && !$isAdmin && !$isGroupMod) {
            return response()->json(['error' => 'You can only delete your own posts.'], 403);
        }

        foreach ($post->media as $media) {
            Storage::disk('public')->delete($media->file_path);
            if ($media->thumbnail_path) {
                Storage::disk('public')->delete($media->thumbnail_path);
            }
        }

        $post->delete();

        return response()->json(['ok' => true, 'post_id' => $id]);
    }

    // ── Like / unlike post ───────────────────────────────────────────────

    public function toggleLike(int $id): JsonResponse
    {
        $myId = (int) session('alumni_id');
        $post = Post::findOrFail($id);

        if (!$this->canAccessGroupPost($post->group_id)) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        $existing = PostLike::where('post_id', $id)->where('alumni_id', $myId)->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            PostLike::create(['post_id' => $id, 'alumni_id' => $myId]);
            $post->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'ok'          => true,
            'is_liked'    => $liked,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    // ── Save / unsave post ───────────────────────────────────────────────

    public function toggleSave(int $id): JsonResponse
    {
        $myId = (int) session('alumni_id');
        $post = Post::findOrFail($id);

        if (!$this->canAccessGroupPost($post->group_id)) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        $existing = PostSave::where('post_id', $id)->where('alumni_id', $myId)->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
        } else {
            PostSave::create(['post_id' => $id, 'alumni_id' => $myId]);
            $saved = true;
        }

        return response()->json(['ok' => true, 'is_saved' => $saved]);
    }

    public function savedPosts(Request $request): JsonResponse
    {
        $myId = (int) session('alumni_id');

        $limit    = max(1, min((int) $request->input('limit', 10), 30));
        $beforeId = (int) $request->input('before_id', 0);

        $query = Post::feed()
            ->whereNull('posts.deleted_at')
            ->whereHas('saves', fn($q) => $q->where('alumni_id', $myId));

        if ($beforeId > 0) {
            $query->where('posts.id', '<', $beforeId);
        }

        $posts = $query->limit($limit)->get();

        return response()->json([
            'posts'     => $posts->map(fn($p) => $p->toFeedArray($myId)),
            'has_more'  => $posts->count() === $limit,
            'oldest_id' => $posts->last()?->id,
        ]);
    }

    public function myPosts(Request $request): JsonResponse
    {
        $myId = (int) session('alumni_id');

        $limit    = max(1, min((int) $request->input('limit', 10), 30));
        $beforeId = (int) $request->input('before_id', 0);

        $query = Post::feed()
            ->whereNull('posts.deleted_at')
            ->where('posts.alumni_id', $myId);

        if ($beforeId > 0) {
            $query->where('posts.id', '<', $beforeId);
        }

        $posts = $query->limit($limit)->get();

        return response()->json([
            'posts'     => $posts->map(fn($p) => $p->toFeedArray($myId)),
            'has_more'  => $posts->count() === $limit,
            'oldest_id' => $posts->last()?->id,
        ]);
    }

    // ── Share (repost to feed) ───────────────────────────────────────────

    public function share(Request $request, int $id): JsonResponse
    {
        $myId = (int) session('alumni_id');

        $request->validate([
            'caption' => 'nullable|string|max:2000',
        ]);

        $original = Post::findOrFail($id);

        if (!$this->canAccessGroupPost($original->group_id)) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        if ($original->group_id) {
            return response()->json(['error' => 'Posts inside a group can\'t be reposted to the main feed.'], 422);
        }

        $targetId = $original->isShare() ? $original->shared_post_id : $original->id;
        $target   = $targetId === $original->id ? $original : Post::findOrFail($targetId);

        $share = DB::transaction(function () use ($myId, $request, $target) {
            $share = Post::create([
                'alumni_id'      => $myId,
                'body'           => trim((string) $request->input('caption', '')) ?: null,
                'type'           => $target->type,
                'shared_post_id' => $target->id,
                'group_id'       => null,
            ]);

            $target->increment('shares_count');

            return $share;
        });

        $share->load(['author', 'media', 'sharedPost.author', 'sharedPost.media']);

        return response()->json([
            'post' => $share->toFeedArray($myId),
        ], 201);
    }

    // ── Comments ─────────────────────────────────────────────────────────

    public function comments(Request $request, int $id): JsonResponse
    {
        $myId = (int) session('alumni_id');
        $post = Post::findOrFail($id);

        if (!$this->canAccessGroupPost($post->group_id)) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        $limit    = max(1, min((int) $request->input('limit', 10), 30));
        $beforeId = (int) $request->input('before_id', 0);

        $query = PostComment::where('post_id', $id)
            ->whereNull('parent_id')
            ->with(['author', 'replies.author'])
            ->orderByDesc('created_at');

        if ($beforeId > 0) {
            $query->where('id', '<', $beforeId);
        }

        $comments = $query->limit($limit)->get();

        return response()->json([
            'comments'  => $comments->map(fn($c) => $c->toApiArray($myId)),
            'has_more'  => $comments->count() === $limit,
            'oldest_id' => $comments->last()?->id,
        ]);
    }

    public function storeComment(Request $request, int $id): JsonResponse
    {
        $myId = (int) session('alumni_id');
        $post = Post::findOrFail($id);

        if (!$this->canAccessGroupPost($post->group_id)) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        $request->validate([
            'body'      => 'required|string|max:2000',
            'parent_id' => 'nullable|integer|exists:post_comments,id',
        ]);

        $parentId = $request->input('parent_id');

        if ($parentId) {
            $parent = PostComment::where('id', $parentId)
                ->where('post_id', $id)
                ->first();

            if (!$parent) {
                return response()->json(['error' => 'Comment not found.'], 404);
            }

            if ($parent->isReply()) {
                return response()->json(['error' => 'You can only reply to top-level comments.'], 422);
            }
        }

        $comment = DB::transaction(function () use ($myId, $id, $request, $parentId, $post) {
            $comment = PostComment::create([
                'post_id'   => $id,
                'alumni_id' => $myId,
                'parent_id' => $parentId,
                'body'      => trim($request->input('body')),
            ]);

            if ($parentId) {
                PostComment::where('id', $parentId)->increment('replies_count');
            }

            $post->increment('comments_count');

            return $comment;
        });

        $comment->load('author');

        return response()->json([
            'comment' => $comment->toApiArray($myId, false),
        ], 201);
    }

    /**
     * DELETE /posts/{postId}/comments/{commentId}
     */
    public function destroyComment(int $postId, int $commentId): JsonResponse
    {
        $myId = (int) session('alumni_id');
        $post    = Post::findOrFail($postId);
        $comment = PostComment::where('id', $commentId)->where('post_id', $postId)->firstOrFail();

        if (!$this->canAccessGroupPost($post->group_id)) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        $isOwner = (int) $comment->alumni_id === $myId;
        $isAdmin = in_array(session('alumni_role'), ['admin', 'super_admin']);

        // Group admins/moderators can also remove comments within their group.
        $isGroupMod = $post->group_id && $post->group && $post->group->isGroupModerator($myId);

        if (!$isOwner && !$isAdmin && !$isGroupMod) {
            return response()->json(['error' => 'You can only delete your own comments.'], 403);
        }

        DB::transaction(function () use ($comment, $post) {
            $totalRemoved = 1 + $comment->replies_count;
            $post->decrement('comments_count', min($totalRemoved, $post->comments_count));

            if ($comment->isReply()) {
                PostComment::where('id', $comment->parent_id)
                    ->where('replies_count', '>', 0)
                    ->decrement('replies_count');
            }

            if (!$comment->isReply()) {
                $comment->replies()->delete();
            }

            $comment->delete();
        });

        return response()->json(['ok' => true, 'comment_id' => $commentId]);
    }

    // ── Comment likes ────────────────────────────────────────────────────

    public function toggleCommentLike(int $id): JsonResponse
    {
        $myId    = (int) session('alumni_id');
        $comment = PostComment::with('post')->findOrFail($id);

        if (!$this->canAccessGroupPost($comment->post?->group_id)) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $existing = CommentLike::where('comment_id', $id)->where('alumni_id', $myId)->first();

        if ($existing) {
            $existing->delete();
            $comment->decrement('likes_count');
            $liked = false;
        } else {
            CommentLike::create(['comment_id' => $id, 'alumni_id' => $myId]);
            $comment->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'ok'          => true,
            'is_liked'    => $liked,
            'likes_count' => $comment->fresh()->likes_count,
        ]);
    }

    // ── Single post page ─────────────────────────────────────────────────

    public function show(Post $post)
    {
        if ($post->trashed()) {
            abort(404);
        }

        if (!$this->canAccessGroupPost($post->group_id)) {
            abort(404);
        }

        $myId = (int) session('alumni_id');

        $post->load(['author', 'media', 'sharedPost.author', 'sharedPost.media']);

        return view('community.posts.show', [
            'post' => $post->toFeedArray($myId),
        ]);
    }
}