<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModelWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'model_type',
        'weights',
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
        'peers_connected'
    ];

    protected $casts = [
        'weights' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'model_ready' => 'boolean',
        'accuracy' => 'float',
        'precision' => 'float',
        'recall' => 'float',
        'f1_score' => 'float',
        'total_layers' => 'integer',
        'total_parameters' => 'integer',
        'peers_connected' => 'integer'
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
        $total = 0;
        foreach ($this->weights['layers'] as $layer) {
            $shape = $layer['shape'];
            if (count($shape) === 1) {
                $total += $shape[0];
            } else {
                $product = 1;
                foreach ($shape as $dim) {
                    $product *= $dim;
                }
                $total += $product;
            }
        }
        return $total;
    }

    public function isModelReady(): bool
    {
        return $this->total_layers === 32 && $this->total_parameters > 0;
    }
} 