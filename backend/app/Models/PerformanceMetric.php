<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_name',
        'value',
        'unit',
        'metadata',
        'recorded_at'
    ];

    protected $casts = [
        'value' => 'float',
        'metadata' => 'array',
        'recorded_at' => 'datetime'
    ];

    public function scopeByName($query, $name)
    {
        return $query->where('metric_name', $name);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('recorded_at', '>=', now()->subHours($hours));
    }

    public function scopeAverage($query)
    {
        return $query->selectRaw('metric_name, AVG(value) as average_value, unit')
                    ->groupBy('metric_name', 'unit');
    }
} 