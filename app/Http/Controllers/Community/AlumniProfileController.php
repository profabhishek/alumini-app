<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;

class AlumniProfileController extends Controller
{
    public function show(AlumniUser $alumniUser)
    {
        // Cast to int for safe comparison — is_approved stored as tinyint
        abort_if(
            $alumniUser->role !== 'alumni' || ! $alumniUser->is_approved,
            404
        );

        $isOwnProfile = (int) session('alumni_id') === (int) $alumniUser->id;

        return view('community.alumni.profile', compact('alumniUser', 'isOwnProfile'));
    }
}