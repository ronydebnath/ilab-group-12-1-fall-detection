<?php

namespace App\Http\Controllers;

use App\Models\FallEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class FallEventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/fall-events",
     *     summary="Get a list of fall events",
     *     tags={"Fall Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="elderly_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of fall events",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = FallEvent::query();
        if ($request->has('elderly_id')) {
            $query->where('elderly_id', $request->elderly_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $events = $query->orderByDesc('detected_at')->paginate(20);
        return response()->json($events);
    }

    /**
     * @OA\Post(
     *     path="/fall-events",
     *     summary="Log a new fall event",
     *     tags={"Fall Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"elderly_id","detected_at"},
     *             @OA\Property(property="elderly_id", type="integer", example=1),
     *             @OA\Property(property="detected_at", type="string", format="date-time", example="2024-05-03T12:00:00Z"),
     *             @OA\Property(property="sensor_data", type="object", example={"acc_x": 0.1, "acc_y": 0.2}),
     *             @OA\Property(property="notes", type="string", example="Fall detected in the living room.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Fall event created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/FallEvent")
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
            'elderly_id' => 'required|exists:elderly_profiles,id',
            'detected_at' => 'required|date',
            'sensor_data' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $event = FallEvent::create([
            'elderly_id' => $request->elderly_id,
            'detected_at' => $request->detected_at,
            'status' => 'detected',
            'sensor_data' => $request->sensor_data,
            'notes' => $request->notes,
        ]);
        return response()->json($event, 201);
    }

    /**
     * @OA\Get(
     *     path="/fall-events/{id}",
     *     summary="Get a specific fall event",
     *     tags={"Fall Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fall event details",
     *         @OA\JsonContent(ref="#/components/schemas/FallEvent")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Fall event not found"
     *     )
     * )
     */
    public function show(FallEvent $fallEvent)
    {
        return response()->json($fallEvent);
    }

    /**
     * @OA\Put(
     *     path="/fall-events/{id}",
     *     summary="Update a fall event",
     *     tags={"Fall Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="resolved"),
     *             @OA\Property(property="resolved_at", type="string", format="date-time"),
     *             @OA\Property(property="sensor_data", type="object"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="false_alarm", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fall event updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/FallEvent")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, FallEvent $fallEvent)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:detected,safe,alerted,resolved,false_alarm',
            'resolved_at' => 'nullable|date',
            'sensor_data' => 'nullable|array',
            'notes' => 'nullable|string',
            'false_alarm' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $fallEvent->update($request->only(['status', 'resolved_at', 'sensor_data', 'notes', 'false_alarm']));
        return response()->json($fallEvent);
    }

    /**
     * @OA\Delete(
     *     path="/fall-events/{id}",
     *     summary="Delete a fall event",
     *     tags={"Fall Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function destroy(FallEvent $fallEvent)
    {
        $fallEvent->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    /**
     * @OA\Patch(
     *     path="/fall-events/{id}/false-alarm",
     *     summary="Mark a fall event as a false alarm",
     *     tags={"Fall Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="notes", type="string", example="No fall occurred, device dropped.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fall event marked as false alarm",
     *         @OA\JsonContent(ref="#/components/schemas/FallEvent")
     *     )
     * )
     */
    public function markFalseAlarm(FallEvent $fallEvent, Request $request)
    {
        $fallEvent->update([
            'status' => 'false_alarm',
            'false_alarm' => true,
            'notes' => $request->notes ?? $fallEvent->notes,
            'resolved_at' => now(),
        ]);
        return response()->json($fallEvent);
    }
}
