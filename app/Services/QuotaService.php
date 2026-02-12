<?php

namespace App\Services;

use App\Models\DailyQuota;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Cache;

class QuotaService
{
    /**
     * Check if we can send another email (daily + hourly limits).
     */
    public function canSendEmail(): bool
    {
        return !$this->isDailyLimitReached() && !$this->isHourlyLimitReached();
    }

    /**
     * Check if daily limit is reached.
     */
    public function isDailyLimitReached(): bool
    {
        $quota = DailyQuota::today();
        return $quota->isExhausted();
    }

    /**
     * Check if hourly limit is reached.
     */
    public function isHourlyLimitReached(): bool
    {
        $hourlyLimit = config('bulkemail.hourly_limit', 50);
        $hourlySent = $this->getHourlyCount();
        return $hourlySent >= $hourlyLimit;
    }

    /**
     * Increment the daily sent counter.
     */
    public function incrementCounter(): void
    {
        $quota = DailyQuota::today();
        $quota->increment('sent_count');

        // Clear cached stats
        Cache::forget('quota_stats_' . now()->toDateString());
    }

    /**
     * Get the number of emails sent this hour.
     */
    public function getHourlyCount(): int
    {
        return EmailLog::where('status', 'sent')
            ->where('sent_at', '>=', now()->startOfHour())
            ->count();
    }

    /**
     * Get remaining daily quota.
     */
    public function getRemainingQuota(): int
    {
        return DailyQuota::today()->remaining();
    }

    /**
     * Get daily stats.
     */
    public function getDailyStats(): array
    {
        $quota = DailyQuota::today();

        return Cache::remember('quota_stats_' . now()->toDateString(), 30, function () use ($quota) {
            return [
                'sent_today' => $quota->sent_count,
                'limit' => $quota->limit,
                'remaining' => $quota->remaining(),
                'percentage' => $quota->percentage(),
                'hourly_sent' => $this->getHourlyCount(),
                'hourly_limit' => config('bulkemail.hourly_limit', 50),
                'is_near_limit' => $this->isNearLimit(),
            ];
        });
    }

    /**
     * Check if we're near the daily limit (warning threshold).
     */
    public function isNearLimit(): bool
    {
        $quota = DailyQuota::today();
        $threshold = config('bulkemail.warning_threshold', 80);
        return $quota->percentage() >= $threshold;
    }
}
