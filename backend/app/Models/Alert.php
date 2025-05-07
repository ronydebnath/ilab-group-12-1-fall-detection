<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'severity',
        'status',
        'message',
        'context',
        'triggered_at',
        'resolved_at'
    ];

    protected $casts = [
        'context' => 'array',
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime'
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(AlertNotification::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
                    ->orWhere('status', 'in_progress');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function resolve()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);
    }
} 