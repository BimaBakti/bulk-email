<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'subject',
        'body',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SCHEDULED,
            self::STATUS_PROCESSING,
            self::STATUS_PAUSED,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    // Stat helpers
    public function sentCount(): int
    {
        return $this->recipients()->where('status', 'sent')->count();
    }

    public function failedCount(): int
    {
        return $this->recipients()->where('status', 'failed')->count();
    }

    public function pendingCount(): int
    {
        return $this->recipients()->whereIn('status', ['pending', 'queued'])->count();
    }

    public function totalRecipients(): int
    {
        return $this->recipients()->count();
    }

    public function progressPercentage(): float
    {
        $total = $this->totalRecipients();
        if ($total === 0) return 0;
        return round(($this->sentCount() / $total) * 100, 1);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_PROCESSING, self::STATUS_SCHEDULED]);
    }
}
