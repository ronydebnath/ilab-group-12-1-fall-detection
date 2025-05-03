<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ElderlyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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