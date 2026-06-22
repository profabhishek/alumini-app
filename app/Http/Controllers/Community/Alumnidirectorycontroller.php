<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;
use App\Models\UserBlock;
use Illuminate\Http\Request;

class AlumniDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->input('search', '');
        $department  = $request->input('department', '');
        $passingYear = $request->input('passing_year', '');
        $perPage     = 24;

        $query = AlumniUser::query()
            ->where('role', 'alumni')
            ->where('is_approved', 1);

        // Exclude blocked / blocking users from directory
        $myId = (int) session('alumni_id');
        if ($myId) {
            $blockedIds = UserBlock::mutualIds($myId);
            if (!empty($blockedIds)) {
                $query->whereNotIn('id', $blockedIds);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name',    'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%")
                  ->orWhere('institute',  'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('batch_name', 'like', "%{$search}%")
                  ->orWhere('country',    'like', "%{$search}%");
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        if ($passingYear) {
            $query->where('passing_year', $passingYear);
        }

        // Unfiltered total — always reflects real member count
        $totalAlumni = AlumniUser::where('role', 'alumni')
            ->where('is_approved', 1)
            ->count();

        $alumni = $query
            ->orderBy('full_name')
            ->paginate($perPage)
            ->withQueryString();

        // Filter dropdowns
        $departments = AlumniUser::where('role', 'alumni')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $passingYears = AlumniUser::where('role', 'alumni')
            ->whereNotNull('passing_year')
            ->where('passing_year', '!=', '')
            ->distinct()
            ->orderByDesc('passing_year')
            ->pluck('passing_year');

        $view = request()->routeIs('alumni') ? 'alumni.index' : 'community.alumni.directory_wired';

        return view($view, compact(
            'alumni',
            'totalAlumni',
            'departments',
            'passingYears',
            'search',
            'department',
            'passingYear'
        ));
    }
}