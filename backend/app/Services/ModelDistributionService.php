<?php

namespace App\Services;

use App\Models\ModelWeight;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ModelDistributionService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 5; // seconds

    public function distributeModel(string $version, array $targetNodes): array
    {
        $model = ModelWeight::where('version', $version)->firstOrFail();
        $distributionResults = [];
        $failedNodes = [];

        foreach ($targetNodes as $node) {
            try {
                $result = $this->sendModelToNode($model, $node);
                $distributionResults[$node] = $result;
            } catch (\Exception $e) {
                Log::error("Failed to distribute model to node {$node}: " . $e->getMessage());
                $failedNodes[] = $node;
                $distributionResults[$node] = [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        // Update distribution status
        $model->update([
            'distribution_status' => empty($failedNodes) ? 'completed' : 'partial',
            'distribution_results' => $distributionResults,
            'failed_nodes' => $failedNodes,
            'last_distribution_at' => now()
        ]);

        return $distributionResults;
    }

    private function sendModelToNode(ModelWeight $model, string $node): array
    {
        $retryCount = 0;
        $lastError = null;

        while ($retryCount < self::MAX_RETRIES) {
            try {
                // Prepare model package
                $package = $this->prepareModelPackage($model);

                // Send to node
                $response = $this->sendToNode($node, $package);

                // Verify distribution
                if ($this->verifyDistribution($node, $model->version)) {
                    return [
                        'status' => 'success',
                        'node' => $node,
                        'timestamp' => now()->toIso8601String(),
                        'package_size' => strlen(json_encode($package))
                    ];
                }

                throw new \Exception("Distribution verification failed");
            } catch (\Exception $e) {
                $lastError = $e;
                $retryCount++;
                if ($retryCount < self::MAX_RETRIES) {
                    sleep(self::RETRY_DELAY);
                }
            }
        }

        throw new \Exception("Failed after {$retryCount} retries: " . $lastError->getMessage());
    }

    private function prepareModelPackage(ModelWeight $model): array
    {
        return [
            'version' => $model->version,
            'model_type' => $model->model_type,
            'weights' => $model->weights,
            'metadata' => [
                'architecture' => $model->model_architecture,
                'hyperparameters' => $model->swarm_hyperparameters,
                'validation_metrics' => $model->validation_metrics,
                'performance_metrics' => $model->performance_metrics
            ],
            'distribution_info' => [
                'distributed_at' => now()->toIso8601String(),
                'package_id' => Str::uuid()->toString(),
                'checksum' => $this->calculateChecksum($model->weights)
            ]
        ];
    }

    private function sendToNode(string $node, array $package): array
    {
        // TODO: Implement actual node communication
        // This would typically use ZeroMQ or similar
        return ['status' => 'success'];
    }

    private function verifyDistribution(string $node, string $version): bool
    {
        // TODO: Implement actual verification
        // This would check if the node has received and can load the model
        return true;
    }

    private function calculateChecksum(array $weights): string
    {
        return hash('sha256', json_encode($weights));
    }

    public function getDistributionStatus(string $version): array
    {
        $model = ModelWeight::where('version', $version)->firstOrFail();
        
        return [
            'version' => $model->version,
            'status' => $model->distribution_status,
            'total_nodes' => count($model->distribution_results ?? []),
            'successful_nodes' => count(array_filter($model->distribution_results ?? [], function($result) {
                return $result['status'] === 'success';
            })),
            'failed_nodes' => $model->failed_nodes ?? [],
            'last_distribution' => $model->last_distribution_at,
            'distribution_results' => $model->distribution_results
        ];
    }

    public function retryFailedDistributions(string $version): array
    {
        $model = ModelWeight::where('version', $version)->firstOrFail();
        $failedNodes = $model->failed_nodes ?? [];

        if (empty($failedNodes)) {
            return ['status' => 'no_failed_nodes'];
        }

        return $this->distributeModel($version, $failedNodes);
    }

    public function validateNodeCompatibility(string $node, string $version): bool
    {
        $model = ModelWeight::where('version', $version)->firstOrFail();
        
        // Check node requirements against model requirements
        $nodeCapabilities = $this->getNodeCapabilities($node);
        
        return $this->checkCompatibility($model, $nodeCapabilities);
    }

    private function getNodeCapabilities(string $node): array
    {
        // TODO: Implement actual node capability check
        return [
            'memory' => 1024, // MB
            'storage' => 5120, // MB
            'compute_capability' => 'high',
            'supported_architectures' => ['CNN-LSTM']
        ];
    }

    private function checkCompatibility(ModelWeight $model, array $nodeCapabilities): bool
    {
        // Check memory requirements
        if ($model->memory_usage > $nodeCapabilities['memory']) {
            return false;
        }

        // Check storage requirements
        $modelSize = strlen(json_encode($model->weights)) / 1024 / 1024; // Convert to MB
        if ($modelSize > $nodeCapabilities['storage']) {
            return false;
        }

        // Check architecture compatibility
        if (!in_array($model->model_type, $nodeCapabilities['supported_architectures'])) {
            return false;
        }

        return true;
    }

    public function cleanupOldDistributions(int $daysToKeep = 30): void
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        ModelWeight::where('last_distribution_at', '<', $cutoffDate)
            ->where('is_active', false)
            ->update([
                'distribution_status' => 'archived',
                'distribution_results' => null,
                'failed_nodes' => null
            ]);
    }
} 