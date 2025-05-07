<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_id',
        'channel',
        'recipient',
        'status',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed'
        ]);
    }
} 