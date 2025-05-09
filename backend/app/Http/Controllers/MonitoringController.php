<?php

namespace App\Http\Controllers;

use App\Models\SystemHealthCheck;
use App\Models\PerformanceMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Monitoring",
 *     description="API Endpoints for system monitoring and health checks"
 * )
 */
class MonitoringController extends Controller
{
    /**
     * @OA\Get(
     *     path="/monitoring/health",
     *     summary="Get system health status",
     *     tags={"Monitoring"},
     *     @OA\Response(
     *         response=200,
     *         description="System health status",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="healthy"),
     *             @OA\Property(
     *                 property="components",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="database"),
     *                     @OA\Property(property="status", type="string", example="healthy"),
     *                     @OA\Property(property="message", type="string", example="Connection successful"),
     *                     @OA\Property(
     *                         property="metrics",
     *                         type="object",
     *                         @OA\Property(property="response_time", type="number", format="float", example=0.15)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function healthCheck(): JsonResponse
    {
        $components = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'api' => $this->checkApi()
        ];

        $overallStatus = collect($components)->every(fn($component) => $component['status'] === 'healthy')
            ? 'healthy'
            : 'unhealthy';

        return response()->json([
            'status' => $overallStatus,
            'components' => $components
        ]);
    }

    /**
     * @OA\Get(
     *     path="/monitoring/metrics",
     *     summary="Get system performance metrics",
     *     tags={"Monitoring"},
     *     @OA\Parameter(
     *         name="hours",
     *         in="query",
     *         description="Time range in hours",
     *         required=false,
     *         @OA\Schema(type="integer", default=24)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="System performance metrics",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="metrics",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="cpu_usage"),
     *                     @OA\Property(property="value", type="number", format="float", example=45.5),
     *                     @OA\Property(property="unit", type="string", example="percent"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function metrics(Request $request): JsonResponse
    {
        $hours = $request->input('hours', 24);
        $metrics = PerformanceMetric::recent($hours)
            ->orderBy('recorded_at', 'desc')
            ->get();

        return response()->json(['metrics' => $metrics]);
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            \DB::connection()->getPdo();
            $responseTime = microtime(true) - $start;

            return [
                'name' => 'database',
                'status' => 'healthy',
                'message' => 'Connection successful',
                'metrics' => [
                    'response_time' => round($responseTime, 3)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'database',
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $start = microtime(true);
            \Cache::put('health_check', true, 1);
            $responseTime = microtime(true) - $start;

            return [
                'name' => 'cache',
                'status' => 'healthy',
                'message' => 'Cache is working',
                'metrics' => [
                    'response_time' => round($responseTime, 3)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'cache',
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $start = microtime(true);
            \Storage::disk('local')->put('health_check.txt', 'test');
            \Storage::disk('local')->delete('health_check.txt');
            $responseTime = microtime(true) - $start;

            return [
                'name' => 'storage',
                'status' => 'healthy',
                'message' => 'Storage is accessible',
                'metrics' => [
                    'response_time' => round($responseTime, 3)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'storage',
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkApi(): array
    {
        return [
            'name' => 'api',
            'status' => 'healthy',
            'message' => 'API is responding',
            'metrics' => [
                'response_time' => 0.0
            ]
        ];
    }
} 