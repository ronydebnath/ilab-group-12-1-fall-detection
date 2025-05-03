<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ElderlyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *     schema="ElderlyProfileRequest",
 *     required={"first_name", "last_name", "date_of_birth", "gender", "primary_phone", "current_address", "emergency_contact_name", "emergency_contact_phone", "emergency_contact_relationship", "mobility_status", "vision_status", "hearing_status", "primary_carer_id", "care_level", "preferred_language", "device_status", "living_situation", "activity_level"},
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", example="1940-01-01"),
 *     @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
 *     @OA\Property(property="profile_photo", type="string", nullable=true, example="profile-photos/john.jpg"),
 *     @OA\Property(property="height", type="number", format="float", nullable=true, example=170.5),
 *     @OA\Property(property="weight", type="number", format="float", nullable=true, example=65.2),
 *     @OA\Property(property="blood_type", type="string", nullable=true, example="O+"),
 *     @OA\Property(property="national_id", type="string", nullable=true, example="A123456789"),
 *     @OA\Property(property="primary_phone", type="string", example="+1234567890"),
 *     @OA\Property(property="secondary_phone", type="string", nullable=true, example="+0987654321"),
 *     @OA\Property(property="email", type="string", nullable=true, example="john.doe@example.com"),
 *     @OA\Property(property="current_address", type="string", example="123 Main St, City, Country"),
 *     @OA\Property(property="emergency_contact_name", type="string", example="Jane Doe"),
 *     @OA\Property(property="emergency_contact_phone", type="string", example="+1122334455"),
 *     @OA\Property(property="emergency_contact_relationship", type="string", example="Daughter"),
 *     @OA\Property(property="medical_conditions", type="string", nullable=true, example="Diabetes"),
 *     @OA\Property(property="allergies", type="string", nullable=true, example="Peanuts"),
 *     @OA\Property(property="current_medications", type="string", nullable=true, example="Metformin"),
 *     @OA\Property(property="disabilities", type="string", nullable=true, example="Hearing loss"),
 *     @OA\Property(property="mobility_status", type="string", enum={"independent", "needs_assistance", "wheelchair_bound"}, example="independent"),
 *     @OA\Property(property="vision_status", type="string", enum={"normal", "glasses", "impaired"}, example="normal"),
 *     @OA\Property(property="hearing_status", type="string", enum={"normal", "hearing_aid", "impaired"}, example="normal"),
 *     @OA\Property(property="last_medical_checkup", type="string", format="date", nullable=true, example="2023-12-01"),
 *     @OA\Property(property="primary_carer_id", type="integer", example=2),
 *     @OA\Property(property="secondary_carer_id", type="integer", nullable=true, example=3),
 *     @OA\Property(property="care_level", type="string", enum={"basic", "moderate", "intensive"}, example="basic"),
 *     @OA\Property(property="special_care_instructions", type="string", nullable=true, example="Needs help with medication"),
 *     @OA\Property(property="daily_routine_notes", type="string", nullable=true, example="Walks every morning"),
 *     @OA\Property(property="dietary_restrictions", type="string", nullable=true, example="No sugar"),
 *     @OA\Property(property="preferred_language", type="string", example="English"),
 *     @OA\Property(property="device_id", type="string", nullable=true, example="DEV123456"),
 *     @OA\Property(property="device_status", type="string", enum={"active", "inactive"}, example="active"),
 *     @OA\Property(property="last_device_check", type="string", format="date", nullable=true, example="2024-05-01"),
 *     @OA\Property(property="device_battery_level", type="integer", nullable=true, example=85),
 *     @OA\Property(property="device_location", type="string", nullable=true, example="Home"),
 *     @OA\Property(property="preferred_hospital", type="string", nullable=true, example="City Hospital"),
 *     @OA\Property(property="insurance_information", type="string", nullable=true, example="Provider: ABC Insurance, Policy: 12345"),
 *     @OA\Property(property="living_situation", type="string", enum={"lives_alone", "with_family", "assisted_living"}, example="with_family"),
 *     @OA\Property(property="activity_level", type="string", enum={"active", "moderate", "sedentary"}, example="active"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Prefers vegetarian meals")
 * )
 */
class ElderlyProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/elderly-profiles",
     *     summary="Get all elderly profiles",
     *     tags={"Elderly Profiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of elderly profiles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ElderlyProfile")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $profiles = ElderlyProfile::with(['primaryCarer', 'secondaryCarer'])->get();
        return response()->json($profiles);
    }

    /**
     * @OA\Post(
     *     path="/elderly-profiles",
     *     summary="Create a new elderly profile",
     *     tags={"Elderly Profiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ElderlyProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Profile created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ElderlyProfile")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'profile_photo' => 'nullable|string',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'blood_type' => 'nullable|string|max:5',
            'national_id' => 'nullable|string|max:255',
            'primary_phone' => 'required|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'current_address' => 'required|string',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:255',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'disabilities' => 'nullable|string',
            'mobility_status' => 'required|in:independent,needs_assistance,wheelchair_bound',
            'vision_status' => 'required|in:normal,glasses,impaired',
            'hearing_status' => 'required|in:normal,hearing_aid,impaired',
            'last_medical_checkup' => 'nullable|date',
            'primary_carer_id' => 'required|exists:users,id',
            'secondary_carer_id' => 'nullable|exists:users,id',
            'care_level' => 'required|in:basic,moderate,intensive',
            'special_care_instructions' => 'nullable|string',
            'daily_routine_notes' => 'nullable|string',
            'dietary_restrictions' => 'nullable|string',
            'preferred_language' => 'required|string|max:50',
            'device_id' => 'nullable|string|max:255',
            'device_status' => 'required|in:active,inactive',
            'last_device_check' => 'nullable|date',
            'device_battery_level' => 'nullable|integer|min:0|max:100',
            'device_location' => 'nullable|string|max:255',
            'preferred_hospital' => 'nullable|string|max:255',
            'insurance_information' => 'nullable|string',
            'living_situation' => 'required|in:lives_alone,with_family,assisted_living',
            'activity_level' => 'required|in:active,moderate,sedentary',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile = ElderlyProfile::create($request->all());
        return response()->json($profile, 201);
    }

    /**
     * @OA\Get(
     *     path="/elderly-profiles/{id}",
     *     summary="Get elderly profile by ID",
     *     tags={"Elderly Profiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile details",
     *         @OA\JsonContent(ref="#/components/schemas/ElderlyProfile")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found"
     *     )
     * )
     */
    public function show($id)
    {
        $profile = ElderlyProfile::with(['primaryCarer', 'secondaryCarer'])->findOrFail($id);
        return response()->json($profile);
    }

    /**
     * @OA\Put(
     *     path="/elderly-profiles/{id}",
     *     summary="Update elderly profile",
     *     tags={"Elderly Profiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ElderlyProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ElderlyProfile")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $profile = ElderlyProfile::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'date_of_birth' => 'sometimes|required|date|before:today',
            'gender' => 'sometimes|required|in:male,female,other',
            'profile_photo' => 'nullable|string',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'blood_type' => 'nullable|string|max:5',
            'national_id' => 'nullable|string|max:255',
            'primary_phone' => 'sometimes|required|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'current_address' => 'sometimes|required|string',
            'emergency_contact_name' => 'sometimes|required|string|max:255',
            'emergency_contact_phone' => 'sometimes|required|string|max:20',
            'emergency_contact_relationship' => 'sometimes|required|string|max:255',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'disabilities' => 'nullable|string',
            'mobility_status' => 'sometimes|required|in:independent,needs_assistance,wheelchair_bound',
            'vision_status' => 'sometimes|required|in:normal,glasses,impaired',
            'hearing_status' => 'sometimes|required|in:normal,hearing_aid,impaired',
            'last_medical_checkup' => 'nullable|date',
            'primary_carer_id' => 'sometimes|required|exists:users,id',
            'secondary_carer_id' => 'nullable|exists:users,id',
            'care_level' => 'sometimes|required|in:basic,moderate,intensive',
            'special_care_instructions' => 'nullable|string',
            'daily_routine_notes' => 'nullable|string',
            'dietary_restrictions' => 'nullable|string',
            'preferred_language' => 'sometimes|required|string|max:50',
            'device_id' => 'nullable|string|max:255',
            'device_status' => 'sometimes|required|in:active,inactive',
            'last_device_check' => 'nullable|date',
            'device_battery_level' => 'nullable|integer|min:0|max:100',
            'device_location' => 'nullable|string|max:255',
            'preferred_hospital' => 'nullable|string|max:255',
            'insurance_information' => 'nullable|string',
            'living_situation' => 'sometimes|required|in:lives_alone,with_family,assisted_living',
            'activity_level' => 'sometimes|required|in:active,moderate,sedentary',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile->update($request->all());
        return response()->json($profile);
    }

    /**
     * @OA\Delete(
     *     path="/elderly-profiles/{id}",
     *     summary="Delete elderly profile",
     *     tags={"Elderly Profiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $profile = ElderlyProfile::findOrFail($id);
        $profile->delete();
        return response()->json(['message' => 'Profile deleted successfully']);
    }
} 