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
        'is_active'
    ];

    protected $casts = [
        'weights' => 'array',
        'metadata' => 'array',
        'accuracy' => 'float',
        'precision' => 'float',
        'recall' => 'float',
        'f1_score' => 'float',
        'is_active' => 'boolean'
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
} 