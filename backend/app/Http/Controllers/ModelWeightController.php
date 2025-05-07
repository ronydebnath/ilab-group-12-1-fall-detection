<?php

namespace App\Http\Controllers;

use App\Services\ModelWeightService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Model Weights",
 *     description="API Endpoints for managing model weights"
 * )
 */
class ModelWeightController extends Controller
{
    protected $modelWeightService;

    public function __construct(ModelWeightService $modelWeightService)
    {
        $this->modelWeightService = $modelWeightService;
    }

    /**
     * @OA\Post(
     *     path="/model-weights",
     *     summary="Store new model weights",
     *     tags={"Model Weights"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"version", "model_type", "weights"},
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="model_type", type="string", example="cnn-lstm"),
     *             @OA\Property(
     *                 property="weights",
     *                 type="object",
     *                 @OA\Property(
     *                     property="layers",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="layer", type="string", example="conv1d_1"),
     *                         @OA\Property(
     *                             property="shape",
     *                             type="array",
     *                             @OA\Items(type="integer"),
     *                             example="[32, 64, 3]"
     *                         ),
     *                         @OA\Property(property="mean", type="number", format="float", example=0.123),
     *                         @OA\Property(property="std", type="number", format="float", example=0.456)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="description", type="string", example="Initial model weights"),
     *             @OA\Property(property="accuracy", type="number", format="float", example=0.95),
     *             @OA\Property(property="precision", type="number", format="float", example=0.94),
     *             @OA\Property(property="recall", type="number", format="float", example=0.93),
     *             @OA\Property(property="f1_score", type="number", format="float", example=0.935),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="node_id", type="string", example="node_1"),
     *                 @OA\Property(property="aggregation_status", type="string", example="pending"),
     *                 @OA\Property(property="peers_connected", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Model weights stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Model weights stored successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="version", type="string", example="1.0.0"),
     *                 @OA\Property(property="model_type", type="string", example="cnn-lstm"),
     *                 @OA\Property(property="weights", type="object"),
     *                 @OA\Property(property="description", type="string", example="Initial model weights"),
     *                 @OA\Property(property="accuracy", type="number", format="float", example=0.95),
     *                 @OA\Property(property="precision", type="number", format="float", example=0.94),
     *                 @OA\Property(property="recall", type="number", format="float", example=0.93),
     *                 @OA\Property(property="f1_score", type="number", format="float", example=0.935),
     *                 @OA\Property(property="metadata", type="object"),
     *                 @OA\Property(property="is_active", type="boolean", example=false),
     *                 @OA\Property(property="total_layers", type="integer", example=32),
     *                 @OA\Property(property="total_parameters", type="integer", example=1500000),
     *                 @OA\Property(property="model_ready", type="boolean", example=true),
     *                 @OA\Property(property="node_id", type="string", example="node_1"),
     *                 @OA\Property(property="aggregation_status", type="string", example="pending"),
     *                 @OA\Property(property="peers_connected", type="integer", example=3),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'weights' => 'required|array',
            'metrics' => 'array',
            'metadata' => 'array'
        ]);

        try {
            $modelWeight = $this->modelWeightService->storeModelWeights(
                $request->input('weights'),
                $request->input('metrics', []),
                $request->input('metadata', [])
            );

            return response()->json([
                'message' => 'Model weights stored successfully',
                'version' => $modelWeight->version,
                'weights_summary' => [
                    'total_layers' => $modelWeight->total_layers,
                    'total_parameters' => $modelWeight->total_parameters,
                    'model_ready' => $modelWeight->model_ready
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to store model weights',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/model-weights/active",
     *     summary="Get active model weights",
     *     tags={"Model Weights"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Active model weights retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Active model weights retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="version", type="string", example="1.0.0"),
     *                 @OA\Property(property="model_type", type="string", example="cnn-lstm"),
     *                 @OA\Property(property="weights", type="object"),
     *                 @OA\Property(property="description", type="string", example="Active model weights"),
     *                 @OA\Property(property="accuracy", type="number", format="float", example=0.95),
     *                 @OA\Property(property="precision", type="number", format="float", example=0.94),
     *                 @OA\Property(property="recall", type="number", format="float", example=0.93),
     *                 @OA\Property(property="f1_score", type="number", format="float", example=0.935),
     *                 @OA\Property(property="metadata", type="object"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="total_layers", type="integer", example=32),
     *                 @OA\Property(property="total_parameters", type="integer", example=1500000),
     *                 @OA\Property(property="model_ready", type="boolean", example=true),
     *                 @OA\Property(property="node_id", type="string", example="node_1"),
     *                 @OA\Property(property="aggregation_status", type="string", example="pending"),
     *                 @OA\Property(property="peers_connected", type="integer", example=3),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active model weights found"
     *     )
     * )
     */
    public function getActive(): JsonResponse
    {
        try {
            $model = $this->modelWeightService->getActiveModelWeights();
            
            if (!$model) {
                return response()->json([
                    'message' => 'No active model found'
                ], 404);
            }

            return response()->json($model);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve active model',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/model-weights/{version}/activate",
     *     summary="Activate a specific model version",
     *     tags={"Model Weights"},
     *     @OA\Parameter(
     *         name="version",
     *         in="path",
     *         required=true,
     *         description="Model version to activate",
     *         @OA\Schema(type="string", example="v1.0.0")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Model activated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Model activated successfully"),
     *             @OA\Property(property="version", type="string", example="v1.0.0")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to activate model"),
     *             @OA\Property(property="error", type="string", example="Model version not found")
     *         )
     *     )
     * )
     */
    public function activate(string $version): JsonResponse
    {
        try {
            $model = $this->modelWeightService->activateModel($version);
            
            return response()->json([
                'message' => 'Model activated successfully',
                'version' => $model->version
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to activate model',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/model-weights/history",
     *     summary="Get model version history",
     *     tags={"Model Weights"},
     *     @OA\Response(
     *         response=200,
     *         description="Model history retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="version", type="string", example="v1.0.0"),
     *                 @OA\Property(property="model_type", type="string", example="CNN-LSTM"),
     *                 @OA\Property(property="accuracy", type="number", format="float", example=0.95),
     *                 @OA\Property(property="precision", type="number", format="float", example=0.94),
     *                 @OA\Property(property="recall", type="number", format="float", example=0.93),
     *                 @OA\Property(property="f1_score", type="number", format="float", example=0.935),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-21T10:00:00Z"),
     *                 @OA\Property(property="weights_summary", type="object",
     *                     @OA\Property(property="total_layers", type="integer", example=32),
     *                     @OA\Property(property="total_parameters", type="integer", example=123456),
     *                     @OA\Property(property="model_ready", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function history(): JsonResponse
    {
        try {
            $history = $this->modelWeightService->getModelHistory();
            
            return response()->json($history);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve model history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/model-weights/{version}",
     *     summary="Delete a specific model version",
     *     tags={"Model Weights"},
     *     @OA\Parameter(
     *         name="version",
     *         in="path",
     *         required=true,
     *         description="Model version to delete",
     *         @OA\Schema(type="string", example="v1.0.0")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Model deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Model deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to delete model"),
     *             @OA\Property(property="error", type="string", example="Model version not found")
     *         )
     *     )
     * )
     */
    public function delete(string $version): JsonResponse
    {
        try {
            $this->modelWeightService->deleteModel($version);
            
            return response()->json([
                'message' => 'Model deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete model',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 