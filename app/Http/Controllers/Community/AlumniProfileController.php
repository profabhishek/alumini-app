<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;
use App\Models\UserBlock;

class AlumniProfileController extends Controller
{
    public function show(AlumniUser $alumniUser)
    {
        abort_if(! $alumniUser->is_approved, 404);

        $viewerId     = (int) session('alumni_id');
        $isOwnProfile = $viewerId === (int) $alumniUser->id;

        // Block checks — admins/super_admins can always view any profile
        $viewerRole   = session('alumni_role');
        $isAdmin      = in_array($viewerRole, ['admin', 'super_admin']);

        $isBlocking   = false;
        $isBlockedBy  = false;

        if ($viewerId && !$isOwnProfile && !$isAdmin) {
            $isBlocking  = UserBlock::isBlocking($viewerId, (int) $alumniUser->id);
            $isBlockedBy = UserBlock::isBlocking((int) $alumniUser->id, $viewerId);
        }

        return view('community.alumni.profile', compact(
            'alumniUser',
            'isOwnProfile',
            'isBlocking',
            'isBlockedBy'
        ));
    }
}