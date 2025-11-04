<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\LoginAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

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
    protected $loginService;

    public function __construct(LoginAttemptService $loginService)
    {
        $this->loginService = $loginService;
    }

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
        $v = Validator::make($request->all(), [
            'voornaam'       => 'required|string|max:255',
            'achternaam'     => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users,email',
            'password'       => 'required|string|min:6|confirmed',
            'telefoonnummer' => 'nullable|string|max:50',
            'afdeling_id'    => 'nullable|integer',
            'role_id'        => 'nullable|integer',
            'account_status' => 'sometimes|boolean',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();

        $user = User::create([
            'voornaam'       => $data['voornaam'],
            'achternaam'     => $data['achternaam'],
            'email'          => $data['email'],
            'password'       => $data['password'],
            'telefoonnummer' => $data['telefoonnummer'] ?? null,
            'afdeling_id'    => $data['afdeling_id'] ?? null,
            'role_id'        => $data['role_id'] ?? null,
            'account_status' => $data['account_status'] ?? true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'User registered successfully',
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
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
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $this->loginService->recordAttempt(null, $request->ip(), false, 'unknown_email');
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        if (! $user->account_status) {
            $this->loginService->recordAttempt($user, $request->ip(), false, 'blocked_account');
            return response()->json(['message' => 'Account is geblokkeerd.'], 403);
        }

        if (! Hash::check($request->password, $user->password)) {
            $this->loginService->recordAttempt($user, $request->ip(), false, 'wrong_password');

            if ($this->loginService->blockIfNeeded($user, $request->ip())) {
                return response()->json(['message' => 'Account is geblokkeerd.'], 403);
            }

            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $this->loginService->recordAttempt($user, $request->ip(), true, null);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
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
