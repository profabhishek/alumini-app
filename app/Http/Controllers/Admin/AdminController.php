<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────

    private function isSelf(AlumniUser $user): bool
    {
        return $user->id === (int) session('alumni_id');
    }

    private function isSuperAdmin(AlumniUser $user): bool
    {
        return $user->role === 'super_admin';
    }

    private function currentRole(): string
    {
        return session('alumni_role', '');
    }

    private function cleanupFiles(AlumniUser $user): void
    {
        if ($user->attachment && $user->attachment !== 'none') {
            Storage::disk('public')->delete('alumni-documents/' . $user->attachment);
        }
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }
    }

    // ── Pending Registration Requests ─────────────────────────────────────

    public function pendingUsers()
    {
        $pendingUsers = AlumniUser::where('is_approved', false)
            ->whereIn('role', ['alumni', 'moderator'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users.pending', compact('pendingUsers'));
    }

    public function approveUser(AlumniUser $user)
    {
        if ($user->is_approved) {
            return back()->with('info', 'This account is already approved.');
        }

        $user->is_approved = true;
        $user->save();

        return back()->with('success', "{$user->full_name}'s account has been approved.");
    }

    public function rejectUser(AlumniUser $user)
    {
        if ($user->is_approved) {
            return back()->with('error', 'Cannot reject an already-approved account from the pending queue.');
        }

        $name = $user->full_name;
        $this->cleanupFiles($user);
        $user->delete();

        return back()->with('success', "{$name}'s registration has been rejected and removed.");
    }

    // ── Admin Management (super_admin only) ──────────────────────────────

    public function index()
    {
        $admins = AlumniUser::whereIn('role', ['admin', 'super_admin'])
            ->orderByRaw("FIELD(role, 'super_admin', 'admin')")
            ->orderBy('full_name')
            ->get();

        return view('admin.users.index', compact('admins'));
    }

    public function createAdminForm()
    {
        return view('admin.users.create-admin');
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:alumni_users,email',
            'password'  => 'required|confirmed|min:8',
            'role'      => 'required|in:admin,super_admin',
        ]);

        AlumniUser::create([
            'full_name'    => $request->full_name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'is_approved'  => true,
            'batch_name'   => 'N/A',
            'phone'        => 'N/A',
            'department'   => 'Administration',
            'passing_year' => date('Y'),
            'roll_number'  => 'ADMIN-' . strtoupper(uniqid()),
            'attachment'   => 'none',
            'birth_date'   => now()->subYears(25)->toDateString(),
            'gender'       => 'Other',
            'institute'    => 'ICCR',
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Admin account for \"{$request->full_name}\" created successfully.");
    }

    public function editAdmin(AlumniUser $user)
    {
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User is not an admin account.');
        }

        // Only super_admin can edit other super_admins
        if ($this->isSuperAdmin($user) && $this->currentRole() !== 'super_admin') {
            abort(403, 'Only super admins can edit other super admin accounts.');
        }

        return view('admin.users.edit-admin', compact('user'));
    }

    public function updateAdmin(Request $request, AlumniUser $user)
    {
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User is not an admin account.');
        }

        if ($this->isSuperAdmin($user) && $this->currentRole() !== 'super_admin') {
            abort(403, 'Only super admins can edit other super admin accounts.');
        }

        $rules = [
            'full_name' => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('alumni_users')->ignore($user->id)],
            'role'      => 'required|in:admin,super_admin',
            'password'  => 'nullable|confirmed|min:8',
        ];

        // Prevent demoting yourself
        if ($this->isSelf($user) && $request->role !== 'super_admin') {
            return back()->with('error', 'You cannot change your own role.');
        }

        $validated = $request->validate($rules);

        $user->full_name = $validated['full_name'];
        $user->email     = $validated['email'];
        $user->role      = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->full_name}'s admin account has been updated.");
    }

    public function destroyAdmin(AlumniUser $user)
    {
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'User is not an admin account.');
        }

        if ($this->isSelf($user)) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($this->isSuperAdmin($user)) {
            return back()->with('error', 'Super admin accounts cannot be deleted from this panel.');
        }

        $name = $user->full_name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "{$name}'s admin account has been deleted.");
    }

    public function revokeAdmin(AlumniUser $user)
    {
        if ($this->isSelf($user)) {
            return back()->with('error', 'You cannot revoke your own admin access.');
        }

        if ($this->isSuperAdmin($user)) {
            return back()->with('error', 'Super admin accounts cannot be revoked from here.');
        }

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'User is not an admin.');
        }

        $user->role = 'alumni';
        $user->save();

        return back()->with('success', "{$user->full_name} has been demoted to alumni.");
    }

    // ── Alumni Management (admin / super_admin) ──────────────────────────

    public function alumniIndex(Request $request)
    {
        $query = AlumniUser::where('role', 'alumni');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('batch_name', 'like', "%{$search}%");
            });
        }

        // Filter by approval status
        if ($request->get('status') === 'approved') {
            $query->where('is_approved', true);
        } elseif ($request->get('status') === 'pending') {
            $query->where('is_approved', false);
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        match($sort) {
            'name'   => $query->orderBy('full_name'),
            'oldest' => $query->orderBy('created_at'),
            default  => $query->orderBy('created_at', 'desc'),
        };

        $alumni = $query->paginate(20)->withQueryString();

        return view('admin.users.alumni-index', compact('alumni'));
    }

    public function alumniShow(AlumniUser $user)
    {
        if ($user->role !== 'alumni') {
            return redirect()->route('admin.alumni.index')
                ->with('error', 'User is not an alumni account.');
        }

        return view('admin.users.alumni-show', compact('user'));
    }

    public function alumniEdit(AlumniUser $user)
    {
        if ($user->role !== 'alumni') {
            return redirect()->route('admin.alumni.index')
                ->with('error', 'User is not an alumni account.');
        }

        return view('admin.users.alumni-edit', compact('user'));
    }

    public function alumniUpdate(Request $request, AlumniUser $user)
    {
        if ($user->role !== 'alumni') {
            return redirect()->route('admin.alumni.index')
                ->with('error', 'User is not an alumni account.');
        }

        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => ['required', 'email', Rule::unique('alumni_users')->ignore($user->id)],
            'phone'        => 'required|string|max:20',
            'department'   => 'required|string|max:255',
            'batch_name'   => 'required|string|max:100',
            'passing_year' => 'required|integer|min:1970|max:' . (date('Y') + 5),
            'institute'    => 'required|string|max:255',
            'roll_number'  => 'required|string|max:100',
            'gender'       => 'required|in:Male,Female,Other',
            'birth_date'   => 'required|date|before:today',
            'country'      => 'nullable|string|max:100',
            'current_city' => 'nullable|string|max:100',
            'is_approved'  => 'boolean',
            'password'     => 'nullable|confirmed|min:8',
        ]);

        $user->fill(collect($validated)->except(['password', 'is_approved'])->toArray());
        $user->is_approved = $request->boolean('is_approved');

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.alumni.show', $user)
            ->with('success', "{$user->full_name}'s profile has been updated.");
    }

    public function alumniDestroy(AlumniUser $user)
    {
        if ($user->role !== 'alumni') {
            return back()->with('error', 'User is not an alumni account.');
        }

        $name = $user->full_name;
        $this->cleanupFiles($user);
        $user->delete();

        return redirect()->route('admin.alumni.index')
            ->with('success', "{$name}'s account has been permanently deleted.");
    }

    public function alumniToggleApproval(AlumniUser $user)
    {
        if ($user->role !== 'alumni') {
            return back()->with('error', 'User is not an alumni account.');
        }

        $user->is_approved = !$user->is_approved;
        $user->save();

        $status = $user->is_approved ? 'approved' : 'suspended';

        return back()->with('success', "{$user->full_name}'s account has been {$status}.");
    }

    // ── Admin badge counts (for sidebar polling) ──────────────────────────

    public function adminBadgeCounts(): JsonResponse
    {
        $role = session('alumni_role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return response()->json(['pending_users' => 0, 'newsletter_new' => 0]);
        }

        $user = AlumniUser::find(session('alumni_id'));

        $pendingUsersSince = $user?->pending_users_last_seen ?? now()->subYear();
        $newsletterSince   = $user?->newsletter_last_seen   ?? now()->subYear();

        return response()->json([
            'pending_users'  => AlumniUser::where('is_approved', false)
                ->whereIn('role', ['alumni', 'moderator'])
                ->where('created_at', '>', $pendingUsersSince)
                ->count(),
            'newsletter_new' => NewsletterSubscriber::where('status', 'active')
                ->where('subscribed_at', '>', $newsletterSince)
                ->count(),
        ]);
    }

    public function markPendingUsersSeen(): JsonResponse
    {
        if (!in_array(session('alumni_role'), ['admin', 'super_admin'])) {
            return response()->json(['ok' => false], 403);
        }
        AlumniUser::where('id', session('alumni_id'))
            ->update(['pending_users_last_seen' => now()]);
        return response()->json(['ok' => true]);
    }

    public function markNewsletterSeen(): JsonResponse
    {
        if (!in_array(session('alumni_role'), ['admin', 'super_admin'])) {
            return response()->json(['ok' => false], 403);
        }
        AlumniUser::where('id', session('alumni_id'))
            ->update(['newsletter_last_seen' => now()]);
        return response()->json(['ok' => true]);
    }
}