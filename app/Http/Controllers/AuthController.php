<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
/**
 * @OA\Info(
 *     title="Geoprofs API",
 *     version="1.0.0"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="loek"),
     *             @OA\Property(property="email", type="string", example="loek@loek.nl"),
     *             @OA\Property(property="password", type="string", example="12345678"),
     *             @OA\Property(property="password_confirmation", type="string", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Show the login page (Inertia + React)
     */
    public function loginPage()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="loek@loek.nl"),
     *             @OA\Property(property="password", type="string", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $this->logAttempt(null, $request->ip(), false, 'unknown_email');

            return response()->json([
                'message' => 'Ongeldige inloggegevens.',
            ], 401);
        }

        if (! Hash::check($request->password, $user->password)) {
            $this->logAttempt($user->user_id, $request->ip(), false, 'wrong_password');

            return response()->json([
                'message' => 'Ongeldige inloggegevens.',
            ], 401);
        }

        Auth::login($user);
        $this->logAttempt($user->user_id, $request->ip(), true, null);

        return response()->json([
            'message' => 'Inloggen gelukt.',
            'user' => $user,
        ]);
    }

    /**
     * API logout (optioneel)
     */
    public function apiLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Uitgelogd.',
        ]);
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

    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Get authenticated user",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="loek"),
     *             @OA\Property(property="email", type="string", example="loek1@loek.nl")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function user(Request $request)
    {
        return $request->user();
    }
    /**
     * Optional: logout API token
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete(); // verwijdert de token uit de database
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

}
