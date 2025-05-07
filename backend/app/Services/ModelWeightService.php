<?php

namespace App\Services;

use App\Models\ModelWeight;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ModelWeightService
{
    public function storeModelWeights(array $weights, array $metrics = [], array $metadata = [])
    {
        try {
            $version = $this->generateVersion();
            
            $modelWeight = ModelWeight::create([
                'version' => $version,
                'model_type' => 'binary_cnn',
                'weights' => $weights,
                'accuracy' => $metrics['accuracy'] ?? null,
                'precision' => $metrics['precision'] ?? null,
                'recall' => $metrics['recall'] ?? null,
                'f1_score' => $metrics['f1_score'] ?? null,
                'metadata' => $metadata,
                'is_active' => false
            ]);

            // Store weights file in storage
            $this->storeWeightsFile($version, $weights);

            return $modelWeight;
        } catch (\Exception $e) {
            Log::error('Error storing model weights: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getActiveModelWeights()
    {
        $model = ModelWeight::where('is_active', true)->first();
        
        if (!$model) {
            return null;
        }

        return [
            'version' => $model->version,
            'weights' => $model->weights,
            'metrics' => [
                'accuracy' => $model->accuracy,
                'precision' => $model->precision,
                'recall' => $model->recall,
                'f1_score' => $model->f1_score
            ],
            'metadata' => $model->metadata
        ];
    }

    public function activateModel(string $version)
    {
        $model = ModelWeight::where('version', $version)->first();
        
        if (!$model) {
            throw new \Exception("Model version not found: {$version}");
        }

        $model->activate();
        return $model;
    }

    public function getModelHistory()
    {
        return ModelWeight::orderBy('created_at', 'desc')
            ->select(['id', 'version', 'model_type', 'accuracy', 'precision', 'recall', 'f1_score', 'is_active', 'created_at'])
            ->get();
    }

    private function generateVersion()
    {
        return 'v' . date('Ymd') . '-' . Str::random(6);
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

    public function deleteModel(string $version)
    {
        $model = ModelWeight::where('version', $version)->first();
        
        if (!$model) {
            throw new \Exception("Model version not found: {$version}");
        }

        // Delete weights file
        $filename = "model_weights/{$version}.json";
        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }

        // Delete database record
        $model->delete();
    }
} 