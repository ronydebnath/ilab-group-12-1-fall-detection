<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Alerts",
 *     description="API Endpoints for alert management"
 * )
 */
class AlertController extends Controller
{
    /**
     * @OA\Post(
     *     path="/alerts",
     *     summary="Trigger a new alert",
     *     tags={"Alerts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "severity", "message"},
     *             @OA\Property(property="type", type="string", example="fall_detected"),
     *             @OA\Property(property="severity", type="string", example="high"),
     *             @OA\Property(property="message", type="string", example="Fall detected for user ID 123"),
     *             @OA\Property(
     *                 property="context",
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=123),
     *                 @OA\Property(property="location", type="string", example="Living Room"),
     *                 @OA\Property(property="confidence", type="number", format="float", example=0.95)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Alert created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Alert created successfully"),
     *             @OA\Property(
     *                 property="alert",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="fall_detected"),
     *                 @OA\Property(property="severity", type="string", example="high"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="message", type="string", example="Fall detected for user ID 123"),
     *                 @OA\Property(property="triggered_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function triggerAlert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'severity' => 'required|string|in:low,medium,high,critical',
            'message' => 'required|string',
            'context' => 'nullable|array'
        ]);

        $alert = Alert::create([
            'type' => $validated['type'],
            'severity' => $validated['severity'],
            'message' => $validated['message'],
            'context' => $validated['context'] ?? null,
            'triggered_at' => now()
        ]);

        // Create notifications for the alert
        $this->createNotifications($alert);

        return response()->json([
            'message' => 'Alert created successfully',
            'alert' => $alert
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/alerts",
     *     summary="Get all alerts",
     *     tags={"Alerts"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by alert status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "in_progress", "resolved"})
     *     ),
     *     @OA\Parameter(
     *         name="severity",
     *         in="query",
     *         description="Filter by alert severity",
     *         required=false,
     *         @OA\Schema(type="string", enum={"low", "medium", "high", "critical"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of alerts",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="alerts",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(property="severity", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="message", type="string"),
     *                     @OA\Property(property="triggered_at", type="string", format="date-time"),
     *                     @OA\Property(property="resolved_at", type="string", format="date-time", nullable=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Alert::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        $alerts = $query->orderBy('triggered_at', 'desc')->get();

        return response()->json(['alerts' => $alerts]);
    }

    /**
     * @OA\Post(
     *     path="/alerts/{id}/resolve",
     *     summary="Resolve an alert",
     *     tags={"Alerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alert resolved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Alert resolved successfully"),
     *             @OA\Property(
     *                 property="alert",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="status", type="string", example="resolved"),
     *                 @OA\Property(property="resolved_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alert not found"
     *     )
     * )
     */
    public function resolve(Alert $alert): JsonResponse
    {
        $alert->resolve();

        return response()->json([
            'message' => 'Alert resolved successfully',
            'alert' => $alert
        ]);
    }

    private function createNotifications(Alert $alert): void
    {
        // Get notification channels based on alert severity
        $channels = $this->getNotificationChannels($alert->severity);

        foreach ($channels as $channel) {
            AlertNotification::create([
                'alert_id' => $alert->id,
                'channel' => $channel,
                'recipient' => $this->getRecipientForChannel($channel),
                'status' => 'pending'
            ]);
        }
    }

    private function getNotificationChannels(string $severity): array
    {
        return match ($severity) {
            'critical' => ['email', 'sms', 'push'],
            'high' => ['email', 'push'],
            'medium' => ['email'],
            'low' => ['push'],
            default => ['email']
        };
    }

    private function getRecipientForChannel(string $channel): string
    {
        // In a real application, this would fetch the appropriate recipient
        // based on the channel and alert context
        return match ($channel) {
            'email' => 'admin@example.com',
            'sms' => '+1234567890',
            'push' => 'device_token_123',
            default => 'default_recipient'
        };
    }
} 