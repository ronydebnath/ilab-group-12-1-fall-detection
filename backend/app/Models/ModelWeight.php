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
        'error_logs'
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
        'last_validation_at' => 'datetime'
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
} 