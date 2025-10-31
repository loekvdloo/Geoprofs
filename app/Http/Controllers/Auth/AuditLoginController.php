<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\LoginAttempt;

class AuditLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Als gebruiker niet bestaat
        if (!$user) {
            $this->logAttempt(null, $request->ip(), false, 'unknown_email');
            return back()->withErrors(['email' => 'Gebruiker niet gevonden.']);
        }

        // Als wachtwoord niet klopt
        if (!Hash::check($request->password, $user->password)) {
            $this->logAttempt($user->user_id, $request->ip(), false, 'wrong_password');
            return back()->withErrors(['password' => 'Onjuist wachtwoord.']);
        }

        // Als login klopt
        Auth::login($user);
        $this->logAttempt($user->user_id, $request->ip(), true, null);

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function logAttempt($userId, $ip, $success, $reason)
    {
        LoginAttempt::create([
            'user_id' => $userId,
            'attempt_time' => now(),
            'attempt_ip' => $ip,
            'succes' => $success,
            'failure_reason' => $reason,
        ]);
    }
}
