<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Post;

class GroupController extends Controller
{
    // ── Directory ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $myId = (int) session('alumni_id');

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

    return view('community.groups.show', compact(
        'group', 'isApproved', 'isPending', 'groupRole',
        'isGroupAdmin', 'isGroupMod', 'isSiteAdmin', 'pendingCount',
        'pendingEditsCount'
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

        // Moderators (non-admin) may only remove plain members.
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
}