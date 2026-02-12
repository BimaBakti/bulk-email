<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'email',
        'name',
        'custom_fields',
        'status',
        'sent_at',
        'error_message',
        'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    const STATUS_PENDING = 'pending';
    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function canRetry(): bool
    {
        return $this->status === self::STATUS_FAILED
            && $this->retry_count < config('bulkemail.max_retry_attempts', 3);
    }
}
