<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelWeight extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'version',
        'model_type',
        'weights',
        'description',
        'accuracy',
        'precision',
        'recall',
        'f1_score',
        'metadata',
        'is_active',
        'total_layers',
        'total_parameters',
        'model_ready',
        'node_id',
        'aggregation_status',
        'peers_connected',
        'encryption_key_id',
        'encryption_algorithm',
        'encryption_iv',
        'training_metadata',
        'training_epochs',
        'learning_rate',
        'optimizer',
        'hyperparameters',
        'client_version',
        'client_platform',
        'client_device_id',
        'client_os_version',
        'performance_metrics',
        'inference_time',
        'memory_usage',
        'battery_impact',
        'aggregation_method',
        'round_number',
        'participating_nodes',
        'aggregation_confidence',
        'model_architecture',
        'input_shape',
        'output_shape',
        'layer_configurations',
        'validation_accuracy',
        'validation_loss',
        'validation_metrics',
        'quality_check_passed',
        'last_sync_at',
        'last_validation_at',
        'sync_status',
        'error_logs',
        'swarm_id',
        'swarm_round',
        'swarm_metrics',
        'swarm_contributors',
        'swarm_contribution_score',
        'swarm_hyperparameters',
        'swarm_aggregation_strategy',
        'swarm_convergence_score',
        'swarm_validation_results',
        'swarm_last_updated',
        'swarm_status'
    ];

    protected $casts = [
        'weights' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'model_ready' => 'boolean',
        'peers_connected' => 'integer',
        'total_layers' => 'integer',
        'total_parameters' => 'integer',
        'training_metadata' => 'array',
        'hyperparameters' => 'array',
        'performance_metrics' => 'array',
        'participating_nodes' => 'array',
        'model_architecture' => 'array',
        'layer_configurations' => 'array',
        'validation_metrics' => 'array',
        'error_logs' => 'array',
        'inference_time' => 'float',
        'memory_usage' => 'float',
        'battery_impact' => 'float',
        'learning_rate' => 'float',
        'validation_accuracy' => 'float',
        'validation_loss' => 'float',
        'aggregation_confidence' => 'float',
        'quality_check_passed' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_validation_at' => 'datetime',
        'swarm_metrics' => 'array',
        'swarm_contributors' => 'array',
        'swarm_hyperparameters' => 'array',
        'swarm_validation_results' => 'array',
        'swarm_contribution_score' => 'float',
        'swarm_convergence_score' => 'float',
        'swarm_last_updated' => 'datetime',
        'swarm_round' => 'integer',
        'swarm_status' => 'string'
    ];

    public function activate()
    {
        // Deactivate all other models
        self::where('is_active', true)->update(['is_active' => false]);
        
        // Activate this model
        $this->is_active = true;
        $this->save();
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function getActiveModel()
    {
        return self::where('is_active', true)->first();
    }

    public function getLatestModel()
    {
        return self::latest()->first();
    }

    public function getModelByVersion($version)
    {
        return self::where('version', $version)->first();
    }

    public function calculateTotalParameters(): int
    {
        if (empty($this->weights['layers'])) {
            return 0;
        }

        $total = 0;
        foreach ($this->weights['layers'] as $layer) {
            if (isset($layer['shape'])) {
                $total += array_reduce($layer['shape'], function($carry, $item) {
                    return $carry * $item;
                }, 1);
            }
        }
        return $total;
    }

    public function isModelReady(): bool
    {
        return $this->quality_check_passed &&
               $this->validation_accuracy >= 0.85 &&
               $this->inference_time < 1000 && // Less than 1 second
               $this->memory_usage < 500; // Less than 500MB
    }

    public static function getLatestVersion(): ?string
    {
        return static::orderBy('created_at', 'desc')
                    ->value('version');
    }

    public function scopeByClientVersion($query, $version)
    {
        return $query->where('client_version', $version);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeReady($query)
    {
        return $query->where('model_ready', true)
                    ->where('quality_check_passed', true);
    }

    public function scopeByAggregationStatus($query, $status)
    {
        return $query->where('aggregation_status', $status);
    }

    public function scopePendingSync($query)
    {
        return $query->where('sync_status', 'pending')
            ->where('quality_check_passed', true);
    }

    public function scopeCompletedSync($query)
    {
        return $query->where('sync_status', 'completed');
    }

    public function scopeFailedSync($query)
    {
        return $query->where('sync_status', 'failed');
    }

    public function scopeByNode($query, string $nodeId)
    {
        return $query->where('node_id', $nodeId);
    }

    public function scopeByVersion($query, string $version)
    {
        return $query->where('version', $version);
    }

    public function scopeByRound($query, int $roundNumber)
    {
        return $query->where('round_number', $roundNumber);
    }

    public function scopeByAggregationMethod($query, string $method)
    {
        return $query->where('aggregation_method', $method);
    }

    public function scopeQualityChecked($query)
    {
        return $query->where('quality_check_passed', true);
    }

    public function getTrainingMetadataAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getPerformanceMetricsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getParticipatingNodesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getModelArchitectureAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getValidationMetricsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getErrorLogsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function isSyncPending(): bool
    {
        return $this->sync_status === 'pending';
    }

    public function isSyncCompleted(): bool
    {
        return $this->sync_status === 'completed';
    }

    public function isSyncFailed(): bool
    {
        return $this->sync_status === 'failed';
    }

    public function markAsSynced(): void
    {
        $this->update([
            'sync_status' => 'completed',
            'last_sync_at' => now()
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'sync_status' => 'failed',
            'error_logs' => array_merge($this->error_logs ?? [], [$error])
        ]);
    }

    public function meetsQualityThresholds(): bool
    {
        return $this->quality_check_passed;
    }

    public function getAggregationConfidence(): float
    {
        return $this->aggregation_confidence ?? 0.0;
    }

    public function getParticipatingNodesCount(): int
    {
        return count($this->participating_nodes ?? []);
    }

    public function getTrainingMetadata(): array
    {
        return $this->training_metadata ?? [];
    }

    public function getPerformanceMetrics(): array
    {
        return $this->performance_metrics ?? [];
    }

    public function getValidationMetrics(): array
    {
        return $this->validation_metrics ?? [];
    }

    public function getModelArchitecture(): array
    {
        return $this->model_architecture ?? [];
    }

    public function scopeBySwarm($query, string $swarmId)
    {
        return $query->where('swarm_id', $swarmId);
    }

    public function scopeBySwarmRound($query, int $round)
    {
        return $query->where('swarm_round', $round);
    }

    public function scopeBySwarmStatus($query, string $status)
    {
        return $query->where('swarm_status', $status);
    }

    public function getSwarmMetricsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getSwarmContributorsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getSwarmHyperparametersAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getSwarmValidationResultsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function isSwarmActive(): bool
    {
        return $this->swarm_status === 'active';
    }

    public function isSwarmConverged(): bool
    {
        return $this->swarm_convergence_score >= 0.95;
    }

    public function getSwarmContributionScore(): float
    {
        return $this->swarm_contribution_score ?? 0.0;
    }

    public function getSwarmConvergenceScore(): float
    {
        return $this->swarm_convergence_score ?? 0.0;
    }

    public function getSwarmContributorsCount(): int
    {
        return count($this->swarm_contributors ?? []);
    }

    public function updateSwarmStatus(string $status): void
    {
        $this->update([
            'swarm_status' => $status,
            'swarm_last_updated' => now()
        ]);
    }

    public function updateSwarmMetrics(array $metrics): void
    {
        $this->update([
            'swarm_metrics' => array_merge($this->swarm_metrics ?? [], $metrics),
            'swarm_last_updated' => now()
        ]);
    }

    public function addSwarmContributor(string $nodeId, float $contributionScore): void
    {
        $contributors = $this->swarm_contributors ?? [];
        $contributors[$nodeId] = [
            'contribution_score' => $contributionScore,
            'contributed_at' => now()->toIso8601String()
        ];

        $this->update([
            'swarm_contributors' => $contributors,
            'swarm_last_updated' => now()
        ]);
    }

    public function updateSwarmValidationResults(array $results): void
    {
        $this->update([
            'swarm_validation_results' => array_merge($this->swarm_validation_results ?? [], $results),
            'swarm_last_updated' => now()
        ]);
    }

    public function incrementSwarmRound(): void
    {
        $this->update([
            'swarm_round' => $this->swarm_round + 1,
            'swarm_last_updated' => now()
        ]);
    }

    public function updateSwarmConvergenceScore(float $score): void
    {
        $this->update([
            'swarm_convergence_score' => $score,
            'swarm_last_updated' => now()
        ]);
    }

    public function updateSwarmAggregationStrategy(string $strategy): void
    {
        $this->update([
            'swarm_aggregation_strategy' => $strategy,
            'swarm_last_updated' => now()
        ]);
    }

    public function updateSwarmHyperparameters(array $hyperparameters): void
    {
        $this->update([
            'swarm_hyperparameters' => array_merge($this->swarm_hyperparameters ?? [], $hyperparameters),
            'swarm_last_updated' => now()
        ]);
    }
} 