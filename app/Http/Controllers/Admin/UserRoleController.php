<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Afdeling;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Admin - Users",
 *     description="Beheer van gebruikers, rollen en afdelingen"
 * )
 */
class UserRoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/users",
     *     summary="Haal alle gebruikers op (admin)",
     *     tags={"Admin - Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lijst van gebruikers",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="voornaam", type="string", example="Loek"),
     *                 @OA\Property(property="achternaam", type="string", example="de Vries"),
     *                 @OA\Property(property="email", type="string", example="loek@example.com"),
     *                 @OA\Property(property="role", type="object",
     *                     @OA\Property(property="role_id", type="integer", example=1),
     *                     @OA\Property(property="role_naam", type="string", example="Admin")
     *                 ),
     *                 @OA\Property(property="afdeling", type="object",
     *                     @OA\Property(property="afdeling_id", type="integer", example=2),
     *                     @OA\Property(property="afdeling_naam", type="string", example="IT")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Geen rechten"),
     *     @OA\Response(response=401, description="Niet geautoriseerd")
     * )
     */
    public function index(Request $request)
    {
        if ((int) $request->user()->role_id !== 1) {
            return response()->json(['message' => 'Geen rechten'], 403);
        }

        $users = User::with(['role', 'afdeling'])->get();

        return response()->json($users);
    }

    /**
     * @OA\Put(
     *     path="/admin/users/{id}/role-afdeling",
     *     summary="Update rol en afdeling van een gebruiker (admin)",
     *     tags={"Admin - Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID van de gebruiker",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role_id","afdeling_id"},
     *             @OA\Property(property="role_id", type="integer", example=2),
     *             @OA\Property(property="afdeling_id", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rol en afdeling bijgewerkt",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rol en afdeling bijgewerkt"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="voornaam", type="string", example="Loek"),
     *                 @OA\Property(property="achternaam", type="string", example="de Vries"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="afdeling_id", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Geen rechten"),
     *     @OA\Response(response=401, description="Niet geautoriseerd")
     * )
     */
    public function updateRoleAfdeling(Request $request, $id)
    {
        if ((int) $request->user()->role_id !== 1) {
            return response()->json(['message' => 'Geen rechten'], 403);
        }

        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,role_id',
            'afdeling_id' => 'required|integer|exists:afdeling,afdeling_id',
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        return response()->json([
            'message' => 'Rol en afdeling bijgewerkt',
            'user' => $user,
        ]);
    }
}
