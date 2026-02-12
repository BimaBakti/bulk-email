<?php

namespace App\Jobs;

use App\Models\DailyQuota;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetDailyQuotaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Create quota record for today if it doesn't exist
        DailyQuota::firstOrCreate(
            ['date' => now()->toDateString()],
            ['sent_count' => 0, 'limit' => config('bulkemail.daily_limit', 400)]
        );
    }
}
