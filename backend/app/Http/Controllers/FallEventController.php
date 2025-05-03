<?php

namespace App\Http\Controllers;

use App\Models\FallEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class FallEventController extends Controller
{
    /**
     * Display a listing of the fall events.
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
     * Store a newly created fall event in storage.
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
     * Display the specified fall event.
     */
    public function show(FallEvent $fallEvent)
    {
        return response()->json($fallEvent);
    }

    /**
     * Update the specified fall event in storage.
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
     * Remove the specified fall event from storage.
     */
    public function destroy(FallEvent $fallEvent)
    {
        $fallEvent->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    /**
     * Mark a fall event as a false alarm.
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
