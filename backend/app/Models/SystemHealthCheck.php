<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemHealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'component',
        'status',
        'metrics',
        'message',
        'checked_at'
    ];

    protected $casts = [
        'metrics' => 'array',
        'checked_at' => 'datetime'
    ];

    public function scopeHealthy($query)
    {
        return $query->where('status', 'healthy');
    }

    public function scopeUnhealthy($query)
    {
        return $query->where('status', '!=', 'healthy');
    }

    public function scopeByComponent($query, $component)
    {
        return $query->where('component', $component);
    }
} 