<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;

class AlumniProfileController extends Controller
{
    public function show(AlumniUser $alumniUser)
    {
        abort_if(! $alumniUser->is_approved, 404);

        $isOwnProfile = (int) session('alumni_id') === (int) $alumniUser->id;

        return view('community.alumni.profile', compact('alumniUser', 'isOwnProfile'));
    }
}