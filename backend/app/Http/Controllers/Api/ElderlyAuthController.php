<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ElderlyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Elderly Authentication",
 *     description="API Endpoints for elderly user authentication"
 * )
 */
class ElderlyAuthController extends Controller
{
    /**
     * Register a new elderly user.
     * 
     * @OA\Post(
     *     path="/elderly/register",
     *     tags={"Elderly Authentication"},
     *     summary="Register a new elderly user",
     *     description="Create a new elderly user account with profile information",
     *     operationId="elderlyRegister",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation", "device_name", "first_name", "last_name", "date_of_birth", "gender", "primary_phone", "current_address", "emergency_contact_name", "emergency_contact_phone", "emergency_contact_relationship", "mobility_status", "vision_status", "hearing_status", "care_level", "preferred_language", "device_status", "living_situation", "activity_level"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="device_name", type="string", example="John's iPhone"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1940-01-01"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
     *             @OA\Property(property="primary_phone", type="string", example="+1234567890"),
     *             @OA\Property(property="current_address", type="string", example="123 Main St"),
     *             @OA\Property(property="emergency_contact_name", type="string", example="Jane Doe"),
     *             @OA\Property(property="emergency_contact_phone", type="string", example="+1987654321"),
     *             @OA\Property(property="emergency_contact_relationship", type="string", example="Daughter"),
     *             @OA\Property(property="mobility_status", type="string", enum={"independent", "needs_assistance", "wheelchair_bound"}, example="independent"),
     *             @OA\Property(property="vision_status", type="string", enum={"normal", "glasses", "impaired"}, example="normal"),
     *             @OA\Property(property="hearing_status", type="string", enum={"normal", "hearing_aid", "impaired"}, example="normal"),
     *             @OA\Property(property="care_level", type="string", enum={"basic", "moderate", "intensive"}, example="basic"),
     *             @OA\Property(property="preferred_language", type="string", example="English"),
     *             @OA\Property(property="device_status", type="string", enum={"active", "inactive"}, example="active"),
     *             @OA\Property(property="living_situation", type="string", enum={"lives_alone", "with_family", "assisted_living"}, example="with_family"),
     *             @OA\Property(property="activity_level", type="string", enum={"active", "moderate", "sedentary"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email has already been taken."))
     *             )
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'required|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'primary_phone' => 'required|string',
            'current_address' => 'required|string',
            'emergency_contact_name' => 'required|string',
            'emergency_contact_phone' => 'required|string',
            'emergency_contact_relationship' => 'required|string',
            'mobility_status' => 'required|in:independent,needs_assistance,wheelchair_bound',
            'vision_status' => 'required|in:normal,glasses,impaired',
            'hearing_status' => 'required|in:normal,hearing_aid,impaired',
            'care_level' => 'required|in:basic,moderate,intensive',
            'preferred_language' => 'required|string',
            'device_status' => 'required|in:active,inactive',
            'living_situation' => 'required|in:lives_alone,with_family,assisted_living',
            'activity_level' => 'required|in:active,moderate,sedentary',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'elderly',
        ]);

        $elderlyProfile = ElderlyProfile::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'primary_phone' => $request->primary_phone,
            'current_address' => $request->current_address,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relationship' => $request->emergency_contact_relationship,
            'mobility_status' => $request->mobility_status,
            'vision_status' => $request->vision_status,
            'hearing_status' => $request->hearing_status,
            'care_level' => $request->care_level,
            'preferred_language' => $request->preferred_language,
            'device_status' => $request->device_status,
            'living_situation' => $request->living_situation,
            'activity_level' => $request->activity_level,
        ]);

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => $user->load('elderlyProfile')
        ], 201);
    }

    /**
     * Login an elderly user.
     * 
     * @OA\Post(
     *     path="/elderly/login",
     *     tags={"Elderly Authentication"},
     *     summary="Login an elderly user",
     *     description="Authenticate an elderly user and return an access token",
     *     operationId="elderlyLogin",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "device_name"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="device_name", type="string", example="John's iPhone")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The provided credentials are incorrect."))
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)
                    ->where('role', 'elderly')
                    ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => $user->load('elderlyProfile')
        ]);
    }

    /**
     * Logout an elderly user.
     * 
     * @OA\Post(
     *     path="/elderly/logout",
     *     tags={"Elderly Authentication"},
     *     summary="Logout an elderly user",
     *     description="Invalidate the current access token",
     *     operationId="elderlyLogout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get the authenticated elderly user.
     * 
     * @OA\Get(
     *     path="/elderly/user",
     *     tags={"Elderly Authentication"},
     *     summary="Get authenticated elderly user",
     *     description="Get the currently authenticated elderly user's information",
     *     operationId="elderlyUser",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function user(Request $request)
    {
        return response()->json($request->user()->load('elderlyProfile'));
    }
} 