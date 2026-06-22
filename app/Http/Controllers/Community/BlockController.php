<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;
use App\Models\UserBlock;
use Illuminate\Http\JsonResponse;

class BlockController extends Controller
{
    public function block(int $userId): JsonResponse
    {
        $myId = (int) session('alumni_id');

        if ($myId === $userId) {
            return response()->json(['error' => 'You cannot block yourself.'], 422);
        }

        $target = AlumniUser::where('id', $userId)->where('is_approved', true)->first();

        if (!$target) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Idempotent — ignore if already blocked
        UserBlock::firstOrCreate([
            'blocker_id' => $myId,
            'blocked_id' => $userId,
        ]);

        return response()->json(['ok' => true, 'blocked' => true]);
    }

    public function unblock(int $userId): JsonResponse
    {
        $myId = (int) session('alumni_id');

        UserBlock::where('blocker_id', $myId)
            ->where('blocked_id', $userId)
            ->delete();

        return response()->json(['ok' => true, 'blocked' => false]);
    }
}
