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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"weights"},
     *             @OA\Property(property="weights", type="object", 
     *                 example="{
     *                     \"layers\": [
     *                         {\"layer\": 0, \"shape\": [3, 9, 32], \"mean\": 0.010065, \"std\": 0.131482},
     *                         {\"layer\": 1, \"shape\": [32], \"mean\": -0.012182, \"std\": 0.024512},
     *                         {\"layer\": 2, \"shape\": [32], \"mean\": 1.001547, \"std\": 0.031212},
     *                         {\"layer\": 3, \"shape\": [32], \"mean\": 0.005039, \"std\": 0.037787},
     *                         {\"layer\": 4, \"shape\": [32], \"mean\": 0.233742, \"std\": 0.077677},
     *                         {\"layer\": 5, \"shape\": [32], \"mean\": 0.172451, \"std\": 0.085709},
     *                         {\"layer\": 6, \"shape\": [3, 32, 64], \"mean\": 0.001368, \"std\": 0.085847},
     *                         {\"layer\": 7, \"shape\": [64], \"mean\": -0.014205, \"std\": 0.024670},
     *                         {\"layer\": 8, \"shape\": [64], \"mean\": 0.998429, \"std\": 0.026968},
     *                         {\"layer\": 9, \"shape\": [64], \"mean\": 0.011606, \"std\": 0.041174},
     *                         {\"layer\": 10, \"shape\": [64], \"mean\": 0.307916, \"std\": 0.085474},
     *                         {\"layer\": 11, \"shape\": [64], \"mean\": 0.416730, \"std\": 0.324407},
     *                         {\"layer\": 12, \"shape\": [3, 64, 128], \"mean\": 0.000064, \"std\": 0.061577},
     *                         {\"layer\": 13, \"shape\": [128], \"mean\": -0.013466, \"std\": 0.024139},
     *                         {\"layer\": 14, \"shape\": [128], \"mean\": 0.995857, \"std\": 0.024239},
     *                         {\"layer\": 15, \"shape\": [128], \"mean\": -0.000601, \"std\": 0.050114},
     *                         {\"layer\": 16, \"shape\": [128], \"mean\": 0.363162, \"std\": 0.099531},
     *                         {\"layer\": 17, \"shape\": [128], \"mean\": 0.566935, \"std\": 0.409840},
     *                         {\"layer\": 18, \"shape\": [3, 128, 256], \"mean\": 0.001387, \"std\": 0.044957},
     *                         {\"layer\": 19, \"shape\": [256], \"mean\": -0.011954, \"std\": 0.027725},
     *                         {\"layer\": 20, \"shape\": [256], \"mean\": 0.999412, \"std\": 0.025063},
     *                         {\"layer\": 21, \"shape\": [256], \"mean\": 0.013290, \"std\": 0.042457},
     *                         {\"layer\": 22, \"shape\": [256], \"mean\": 0.525387, \"std\": 0.142296},
     *                         {\"layer\": 23, \"shape\": [256], \"mean\": 1.191344, \"std\": 0.605213},
     *                         {\"layer\": 24, \"shape\": [256, 128], \"mean\": -0.001451, \"std\": 0.075050},
     *                         {\"layer\": 25, \"shape\": [128], \"mean\": -0.016712, \"std\": 0.027149},
     *                         {\"layer\": 26, \"shape\": [128], \"mean\": 0.994559, \"std\": 0.029041},
     *                         {\"layer\": 27, \"shape\": [128], \"mean\": 0.007770, \"std\": 0.173220},
     *                         {\"layer\": 28, \"shape\": [128], \"mean\": 0.431206, \"std\": 0.130864},
     *                         {\"layer\": 29, \"shape\": [128], \"mean\": 0.491943, \"std\": 0.289306},
     *                         {\"layer\": 30, \"shape\": [128, 1], \"mean\": 0.001859, \"std\": 0.137779},
     *                         {\"layer\": 31, \"shape\": [1], \"mean\": 0.195700, \"std\": 0.000000}
     *                     ]
     *                 }"
     *             ),
     *             @OA\Property(
     *                 property="metrics",
     *                 type="object",
     *                 @OA\Property(property="accuracy", type="number", format="float", example=0.95),
     *                 @OA\Property(property="precision", type="number", format="float", example=0.94),
     *                 @OA\Property(property="recall", type="number", format="float", example=0.93),
     *                 @OA\Property(property="f1_score", type="number", format="float", example=0.935)
     *             ),
     *             @OA\Property(property="metadata", type="object", 
     *                 example="{
     *                     \"training_epochs\": 100,
     *                     \"batch_size\": 32,
     *                     \"learning_rate\": 0.001,
     *                     \"model_architecture\": \"CNN-LSTM\",
     *                     \"node_id\": \"node1\",
     *                     \"aggregation_status\": \"completed\",
     *                     \"peers_connected\": 2
     *                 }"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Model weights stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Model weights stored successfully"),
     *             @OA\Property(property="version", type="string", example="v1.0.0"),
     *             @OA\Property(property="weights_summary", type="object",
     *                 @OA\Property(property="total_layers", type="integer", example=32),
     *                 @OA\Property(property="total_parameters", type="integer", example=123456),
     *                 @OA\Property(property="model_ready", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to store model weights"),
     *             @OA\Property(property="error", type="string", example="Database connection error")
     *         )
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
     *     summary="Get currently active model weights",
     *     tags={"Model Weights"},
     *     @OA\Response(
     *         response=200,
     *         description="Active model weights retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="version", type="string", example="v1.0.0"),
     *             @OA\Property(property="weights", type="object",
     *                 @OA\Property(property="layers", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="layer", type="integer", example=0),
     *                         @OA\Property(property="shape", type="array", @OA\Items(type="integer"), example=[3, 9, 32]),
     *                         @OA\Property(property="mean", type="number", format="float", example=0.010065),
     *                         @OA\Property(property="std", type="number", format="float", example=0.131482)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="metrics",
     *                 type="object",
     *                 @OA\Property(property="accuracy", type="number", format="float", example=0.95),
     *                 @OA\Property(property="precision", type="number", format="float", example=0.94),
     *                 @OA\Property(property="recall", type="number", format="float", example=0.93),
     *                 @OA\Property(property="f1_score", type="number", format="float", example=0.935)
     *             ),
     *             @OA\Property(property="metadata", type="object",
     *                 @OA\Property(property="training_epochs", type="integer", example=100),
     *                 @OA\Property(property="batch_size", type="integer", example=32),
     *                 @OA\Property(property="learning_rate", type="number", format="float", example=0.001),
     *                 @OA\Property(property="model_architecture", type="string", example="CNN-LSTM"),
     *                 @OA\Property(property="node_id", type="string", example="node1"),
     *                 @OA\Property(property="aggregation_status", type="string", example="completed"),
     *                 @OA\Property(property="peers_connected", type="integer", example=2)
     *             ),
     *             @OA\Property(property="model_ready", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active model found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No active model found")
     *         )
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