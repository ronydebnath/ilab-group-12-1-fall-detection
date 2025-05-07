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
     *             @OA\Property(property="weights", type="object", description="Model weights data"),
     *             @OA\Property(
     *                 property="metrics",
     *                 type="object",
     *                 @OA\Property(property="accuracy", type="number", format="float"),
     *                 @OA\Property(property="precision", type="number", format="float"),
     *                 @OA\Property(property="recall", type="number", format="float"),
     *                 @OA\Property(property="f1_score", type="number", format="float")
     *             ),
     *             @OA\Property(property="metadata", type="object", description="Additional model metadata")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Model weights stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="version", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
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
                'version' => $modelWeight->version
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
     *             @OA\Property(property="version", type="string"),
     *             @OA\Property(property="weights", type="object"),
     *             @OA\Property(
     *                 property="metrics",
     *                 type="object",
     *                 @OA\Property(property="accuracy", type="number", format="float"),
     *                 @OA\Property(property="precision", type="number", format="float"),
     *                 @OA\Property(property="recall", type="number", format="float"),
     *                 @OA\Property(property="f1_score", type="number", format="float")
     *             ),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active model found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
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
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Model activated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="version", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
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
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="version", type="string"),
     *                 @OA\Property(property="model_type", type="string"),
     *                 @OA\Property(property="accuracy", type="number", format="float"),
     *                 @OA\Property(property="precision", type="number", format="float"),
     *                 @OA\Property(property="recall", type="number", format="float"),
     *                 @OA\Property(property="f1_score", type="number", format="float"),
     *                 @OA\Property(property="is_active", type="boolean"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
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
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Model deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
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