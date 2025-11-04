<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoginAttemptService
{
    public function recordAttempt(?User $user, string $ip, bool $success, ?string $reason = null): void
    {
        DB::table('login_attempts')->insert([
            'user_id'        => $user?->user_id,
            'attempt_time'   => now(),
            'attempt_ip'     => $ip,
            'succes'         => $success,
            'failure_reason' => $reason,
        ]);
    }

    public function checkAndBlockIfNeeded(User $user, string $ip): bool
    {
        $lastThree = DB::table('login_attempts')
            ->where('user_id', $user->user_id)
            ->orderByDesc('attempt_time')
            ->limit(3)
            ->get(['succes']);

        if ($lastThree->count() < 3) {
            return false;
        }

        $allFailed = $lastThree->every(fn($row) => (int)$row->succes === 0);
        if (!$allFailed) {
            return false;
        }

        DB::transaction(function () use ($user, $ip) {
            DB::table('users')
                ->where('user_id', $user->user_id)
                ->update([
                    'account_status' => false,
                    'updated_at'     => now(),
                ]);

            DB::table('login_attempts')->insert([
                'user_id'        => $user->user_id,
                'attempt_time'   => now(),
                'attempt_ip'     => $ip,
                'succes'         => false,
                'failure_reason' => 'account_blocked_auto',
            ]);
        });

        return true;
    }
}
