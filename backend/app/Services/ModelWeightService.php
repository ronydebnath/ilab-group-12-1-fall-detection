<?php

namespace App\Services;

use App\Models\ModelWeight;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ModelWeightService
{
    public function storeModelWeights(array $weights, array $metrics = [], array $metadata = []): ModelWeight
    {
        // Calculate total layers and parameters
        $totalLayers = count($weights['layers']);
        $totalParameters = 0;
        foreach ($weights['layers'] as $layer) {
            $shape = $layer['shape'];
            if (count($shape) === 1) {
                $totalParameters += $shape[0];
            } else {
                $product = 1;
                foreach ($shape as $dim) {
                    $product *= $dim;
                }
                $totalParameters += $product;
            }
        }

        // Create new model weight record
        $modelWeight = new ModelWeight([
            'version' => 'v' . Str::random(8),
            'model_type' => $metadata['model_architecture'] ?? 'CNN-LSTM',
            'weights' => $weights,
            'accuracy' => $metrics['accuracy'] ?? null,
            'precision' => $metrics['precision'] ?? null,
            'recall' => $metrics['recall'] ?? null,
            'f1_score' => $metrics['f1_score'] ?? null,
            'metadata' => $metadata,
            'is_active' => false,
            'total_layers' => $totalLayers,
            'total_parameters' => $totalParameters,
            'model_ready' => $totalLayers === 32 && $totalParameters > 0,
            'node_id' => $metadata['node_id'] ?? null,
            'aggregation_status' => $metadata['aggregation_status'] ?? 'pending',
            'peers_connected' => $metadata['peers_connected'] ?? 0
        ]);

        $modelWeight->save();
        return $modelWeight;
    }

    public function getActiveModelWeights(): ?ModelWeight
    {
        return ModelWeight::where('is_active', true)->first();
    }

    public function activateModel(string $version): ModelWeight
    {
        // Deactivate all models
        ModelWeight::where('is_active', true)->update(['is_active' => false]);

        // Activate the specified model
        $model = ModelWeight::where('version', $version)->firstOrFail();
        $model->is_active = true;
        $model->save();

        return $model;
    }

    public function getModelHistory(): array
    {
        return ModelWeight::orderBy('created_at', 'desc')->get()->toArray();
    }

    public function deleteModel(string $version): void
    {
        $model = ModelWeight::where('version', $version)->firstOrFail();
        if ($model->is_active) {
            throw new \Exception('Cannot delete active model');
        }
        $model->delete();
    }

    private function storeWeightsFile(string $version, array $weights)
    {
        $filename = "model_weights/{$version}.json";
        Storage::put($filename, json_encode($weights));
    }

    public function getWeightsFile(string $version)
    {
        $filename = "model_weights/{$version}.json";
        
        if (!Storage::exists($filename)) {
            throw new \Exception("Weights file not found for version: {$version}");
        }

        return json_decode(Storage::get($filename), true);
    }
} 