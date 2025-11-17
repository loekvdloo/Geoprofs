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
    public function __construct(private LoginAttemptService $loginService) {}

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
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ip   = $request->ip();
        $user = User::where('email', $cred['email'])->first();

        // Onbekend e-mailadres → poging loggen zonder user_id + generieke fout
        if (!$user) {
            $this->loginService->recordAttempt(null, $ip, false, 'unknown_email');
            return back()
                ->withErrors(['email' => 'Onjuiste inloggegevens.'])
                ->onlyInput('email');
        }

        // Account al inactief/gebokkeerd → weigeren + loggen
        if ($this->isUserBlocked($user)) {
            $this->loginService->recordAttempt($user, $ip, false, 'blocked_account');
            return back()
                ->withErrors(['email' => 'Account is geblokkeerd.'])
                ->onlyInput('email');
        }

        // Proberen in te loggen
        if (!Auth::attempt($cred, $request->boolean('remember'))) {
            // Mislukte poging loggen
            $this->loginService->recordAttempt($user, $ip, false, 'bad_credentials');

            // Na 3 mislukte pogingen → automatisch blokkeren (account_status = false)
            if ($this->loginService->checkAndBlockIfNeeded($user, $ip)) {
                return back()
                    ->withErrors(['email' => 'Account is automatisch geblokkeerd na 3 mislukte pogingen.'])
                    ->onlyInput('email');
            }

            return back()
                ->withErrors(['email' => 'Onjuiste inloggegevens.'])
                ->onlyInput('email');
        }

        // Gelukt: sessie vernieuwen + geslaagde poging loggen
        $request->session()->regenerate();
        $this->loginService->recordAttempt($user, $ip, true, null);

        return redirect()->intended(route('dashboard'));
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
