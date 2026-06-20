<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove orphaned alumni_sessions rows that were created by the old
     * AuthController login code (no session_id) and deduplicate any
     * remaining rows with the same (alumni_user_id, user_agent) pair —
     * keeping only the most recently active one per device.
     */
    public function up(): void
    {
        // 1. Delete rows that have no session_id (orphaned from old login code)
        DB::table('alumni_sessions')->whereNull('session_id')->delete();

        // 2. For each user, keep only the most recent row per user_agent.
        //    Identify duplicate (alumni_user_id, user_agent) groups and
        //    delete all but the MAX id (most recently created).
        $duplicates = DB::table('alumni_sessions')
            ->select('alumni_user_id', 'user_agent', DB::raw('MAX(id) as keep_id'))
            ->groupBy('alumni_user_id', 'user_agent')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('alumni_sessions')
                ->where('alumni_user_id', $dup->alumni_user_id)
                ->where('user_agent', $dup->user_agent)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }
    }

    public function down(): void
    {
        // Non-reversible data cleanup
    }
};
