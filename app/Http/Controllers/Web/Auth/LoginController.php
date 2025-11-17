<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoginAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function __construct(private LoginAttemptService $loginService)
    {
    }

    /**
     * Toon loginpagina (Inertia).
     */
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Verwerk login.
     * - Logt elke poging
     * - Blokkeert na 3 mislukte pogingen (zet account_status = false)
     * - Weigert inloggen als account al inactief/gebokkeerd is
     */
    public function store(Request $request)
    {
        $cred = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ip = $request->ip();
        $user = User::where('email', $cred['email'])->first();

        // User bestaat niet
        if (!$user) {
            $this->loginService->recordAttempt(null, $ip, false, 'bad_credentials');

            return back()->withErrors([
                'email' => 'Onjuiste inloggegevens.',
            ])->onlyInput('email');
        }

        if ($user->account_status === false) {
            return back()->withErrors([
                'email' => 'Je account is geblokkeerd.',
            ])->onlyInput('email');
        }

        // Wachtwoord fout
        if (!Auth::attempt($cred, $request->boolean('remember'))) {
            $this->loginService->recordAttempt($user, $ip, false, 'wrong_password');

            return back()->withErrors([
                'email' => 'Onjuiste inloggegevens.',
            ])->onlyInput('email');
        }

        // Succesvolle login
        $this->loginService->recordAttempt($user, $ip, true, null);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Log uit.
     */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Helper: bepaalt of user geblokkeerd/inactief is.
     * Vereist dat in App\Models\User $casts is gezet:
     *   protected $casts = ['account_status' => 'boolean'];
     */
    private function isUserBlocked(User $user): bool
    {
        // account_status === false betekent inactief/gebokkeerd
        return $user->account_status === false;
    }
}
