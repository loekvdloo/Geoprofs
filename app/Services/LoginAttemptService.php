<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoginAttemptService
{
    /**
     * Registreert een loginpoging en controleert of het account moet worden geblokkeerd.
     */
    public function recordAttempt(?User $user, string $ip, bool $success, ?string $reason = null): void
    {
        DB::table('login_attempts')->insert([
            'user_id'        => $user?->user_id,
            'attempt_time'   => now(),
            'attempt_ip'     => $ip,
            'succes'         => $success,
            'failure_reason' => $reason,
        ]);

        // Alleen checken als user bestaat en poging is mislukt
        if ($user && !$success) {
            $this->checkForBlock($user);
        }
    }

    /**
     * Controleert of gebruiker 3 opeenvolgende mislukte pogingen heeft
     * en blokkeert het account als dat zo is.
     */
    protected function checkForBlock(User $user): void
    {
        $lastAttempts = DB::table('login_attempts')
            ->where('user_id', $user->user_id)
            ->orderByDesc('attempt_time')
            ->limit(3)
            ->get();

        // Controleer of de laatste 3 pogingen fout zijn
        if ($lastAttempts->count() === 3 && $lastAttempts->every(fn($a) => !$a->succes)) {

            // Blokkeer alleen als account nog niet geblokkeerd is
            if ($user->account_status === true) {
                $user->update(['account_status' => false]);

                // Log éénmalig dat de blokkade automatisch gebeurde
                DB::table('login_attempts')->insert([
                    'user_id' => $user->user_id,
                    'attempt_time' => now(),
                    'attempt_ip' => request()->ip(),
                    'succes' => false,
                    'failure_reason' => 'account_blocked_auto',
                ]);
            }
        }
    }
}
