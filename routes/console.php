<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ResetDailyQuotaJob;
use App\Jobs\CleanupFailedEmailsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily quota reset at midnight
Schedule::job(new ResetDailyQuotaJob)->dailyAt('00:01');

// Cleanup old failed logs weekly
Schedule::job(new CleanupFailedEmailsJob)->weekly();

// Check for scheduled campaigns every hour
Schedule::call(function () {
    $campaigns = \App\Models\Campaign::where('status', \App\Models\Campaign::STATUS_SCHEDULED)
        ->where('scheduled_at', '<=', now())
        ->get();

    foreach ($campaigns as $campaign) {
        app(\App\Services\CampaignService::class)->startCampaign($campaign);
    }
})->hourly();
