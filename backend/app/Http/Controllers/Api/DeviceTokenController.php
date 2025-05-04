<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Post(
 *     path="/device-token",
 *     summary="Update device token for push notifications",
 *     description="Update the FCM device token for the authenticated user to enable push notifications",
 *     tags={"Device Management"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"device_token"},
 *             @OA\Property(
 *                 property="device_token",
 *                 type="string",
 *                 description="Firebase Cloud Messaging (FCM) token for the device",
 *                 example="fcm_token_123456789"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Device token updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Device token updated successfully"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="device_token",
 *                     type="array",
 *                     @OA\Items(type="string", example="The device token field is required.")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
class DeviceTokenController extends Controller
{
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        DB::table('users')->where('id', $user->id)->update(['device_token' => $request->device_token]);

        return response()->json(['message' => 'Device token updated successfully']);
    }
} 