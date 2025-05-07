<?php

namespace App\Services;

use App\Models\ModelWeight;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
            'peers_connected' => $metadata['peers_connected'] ?? 0,
            'training_metadata' => [
                'epochs' => $metadata['epochs'] ?? null,
                'batch_size' => $metadata['batch_size'] ?? null,
                'learning_rate' => $metadata['learning_rate'] ?? null,
                'optimizer' => $metadata['optimizer'] ?? 'adam',
                'loss_function' => $metadata['loss_function'] ?? 'binary_crossentropy'
            ],
            'performance_metrics' => [
                'inference_time' => $metrics['inference_time'] ?? null,
                'memory_usage' => $metrics['memory_usage'] ?? null,
                'battery_impact' => $metrics['battery_impact'] ?? null
            ],
            'aggregation_method' => $metadata['aggregation_method'] ?? 'fedavg',
            'round_number' => $metadata['round_number'] ?? 1,
            'participating_nodes' => $metadata['participating_nodes'] ?? [],
            'aggregation_confidence' => $metrics['aggregation_confidence'] ?? null,
            'model_architecture' => [
                'input_shape' => $metadata['input_shape'] ?? null,
                'output_shape' => $metadata['output_shape'] ?? null,
                'layer_configurations' => $metadata['layer_configurations'] ?? []
            ],
            'validation_metrics' => [
                'validation_accuracy' => $metrics['validation_accuracy'] ?? null,
                'validation_loss' => $metrics['validation_loss'] ?? null
            ],
            'quality_check_passed' => $this->performQualityCheck($weights, $metrics),
            'last_sync_at' => now(),
            'sync_status' => 'pending'
        ]);

        $modelWeight->save();
        return $modelWeight;
    }

    public function getActiveModelWeights(): ?ModelWeight
    {
        return ModelWeight::where('is_active', true)->first();
    }

    public function getLatestModelWeights(): ?ModelWeight
    {
        return ModelWeight::latest()->first();
    }

    public function getModelWeightsByVersion(string $version): ?ModelWeight
    {
        return ModelWeight::where('version', $version)->first();
    }

    public function getModelWeightsByNode(string $nodeId): ?ModelWeight
    {
        return ModelWeight::where('node_id', $nodeId)
            ->where('sync_status', 'completed')
            ->latest()
            ->first();
    }

    public function aggregateWeights(array $weightsList): array
    {
        if (empty($weightsList)) {
            throw new \InvalidArgumentException('No weights provided for aggregation');
        }

        $aggregatedWeights = [];
        $numWeights = count($weightsList);

        // Get the structure of the first weight set
        $firstWeights = $weightsList[0]['weights']['layers'];
        $numLayers = count($firstWeights);

        // Aggregate each layer
        for ($i = 0; $i < $numLayers; $i++) {
            $layerWeights = [];
            foreach ($weightsList as $weightSet) {
                $layerWeights[] = $weightSet['weights']['layers'][$i]['weights'];
            }

            // Average the weights
            $aggregatedLayer = array_map(function($weights) use ($numWeights) {
                return array_map(function($row) use ($numWeights) {
                    return array_map(function($value) use ($numWeights) {
                        return $value / $numWeights;
                    }, $row);
                }, $weights);
            }, $layerWeights);

            $aggregatedWeights[] = [
                'shape' => $firstWeights[$i]['shape'],
                'weights' => $aggregatedLayer
            ];
        }

        return ['layers' => $aggregatedWeights];
    }

    public function validateWeights(array $weights, array $metrics): bool
    {
        // Check if weights have the correct structure
        if (!isset($weights['layers']) || !is_array($weights['layers'])) {
            return false;
        }

        // Validate each layer
        foreach ($weights['layers'] as $layer) {
            if (!isset($layer['shape']) || !isset($layer['weights'])) {
                return false;
            }

            // Check if weights match the shape
            $shape = $layer['shape'];
            $layerWeights = $layer['weights'];
            
            if (!$this->validateLayerShape($layerWeights, $shape)) {
                return false;
            }
        }

        // Validate metrics
        if (!$this->validateMetrics($metrics)) {
            return false;
        }

        return true;
    }

    private function validateLayerShape(array $weights, array $shape): bool
    {
        if (count($shape) === 1) {
            return count($weights) === $shape[0];
        }

        $currentShape = $this->getArrayShape($weights);
        return $currentShape === $shape;
    }

    private function getArrayShape(array $array): array
    {
        $shape = [];
        $current = $array;
        
        while (is_array($current)) {
            $shape[] = count($current);
            $current = $current[0];
        }
        
        return $shape;
    }

    private function validateMetrics(array $metrics): bool
    {
        $requiredMetrics = ['accuracy', 'precision', 'recall', 'f1_score'];
        
        foreach ($requiredMetrics as $metric) {
            if (!isset($metrics[$metric]) || !is_numeric($metrics[$metric])) {
                return false;
            }
            
            $value = $metrics[$metric];
            if ($value < 0 || $value > 1) {
                return false;
            }
        }
        
        return true;
    }

    private function performQualityCheck(array $weights, array $metrics): bool
    {
        // Check if weights are valid
        if (!$this->validateWeights($weights, $metrics)) {
            return false;
        }

        // Check if metrics meet minimum thresholds
        $thresholds = [
            'accuracy' => 0.85,
            'precision' => 0.80,
            'recall' => 0.80,
            'f1_score' => 0.80
        ];

        foreach ($thresholds as $metric => $threshold) {
            if (!isset($metrics[$metric]) || $metrics[$metric] < $threshold) {
                return false;
            }
        }

        return true;
    }

    public function updateSyncStatus(string $version, string $status, ?string $error = null): void
    {
        $modelWeight = ModelWeight::where('version', $version)->first();
        if ($modelWeight) {
            $modelWeight->update([
                'sync_status' => $status,
                'error_logs' => $error ? array_merge($modelWeight->error_logs ?? [], [$error]) : $modelWeight->error_logs,
                'last_sync_at' => now()
            ]);
        }
    }

    public function getPendingSyncs(): \Illuminate\Database\Eloquent\Collection
    {
        return ModelWeight::where('sync_status', 'pending')
            ->where('quality_check_passed', true)
            ->get();
    }

    public function cleanupOldWeights(int $daysToKeep = 30): void
    {
        $cutoffDate = now()->subDays($daysToKeep);
        ModelWeight::where('created_at', '<', $cutoffDate)
            ->where('is_active', false)
            ->delete();
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