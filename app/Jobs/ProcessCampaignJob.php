<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Recipient;
use App\Services\QuotaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hour max

    public function __construct(
        public Campaign $campaign
    ) {}

    public function handle(QuotaService $quotaService): void
    {
        $campaign = $this->campaign->fresh();

        // Check if campaign is still active
        if (!in_array($campaign->status, [Campaign::STATUS_PROCESSING])) {
            Log::info("Campaign {$campaign->id} is no longer processing. Status: {$campaign->status}");
            return;
        }

        $delay = config('bulkemail.delay_between_emails', 15);
        $batchSize = config('bulkemail.batch_size', 10);

        // Get pending recipients in batches
        $pendingRecipients = $campaign->recipients()
            ->where('status', Recipient::STATUS_PENDING)
            ->limit($batchSize)
            ->get();

        if ($pendingRecipients->isEmpty()) {
            // All recipients processed
            $campaign->update([
                'status' => Campaign::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
            Log::info("Campaign {$campaign->id} completed.");
            return;
        }

        foreach ($pendingRecipients as $index => $recipient) {
            // Check if campaign was paused/stopped
            $campaign->refresh();
            if ($campaign->status !== Campaign::STATUS_PROCESSING) {
                Log::info("Campaign {$campaign->id} paused/stopped during processing.");
                return;
            }

            // Check quota
            if (!$quotaService->canSendEmail()) {
                Log::warning("Quota exceeded during campaign {$campaign->id}. Will resume later.");
                return;
            }

            // Mark as queued
            $recipient->update(['status' => Recipient::STATUS_QUEUED]);

            // Dispatch individual email job with delay
            SendBulkEmailJob::dispatch($recipient, $campaign)
                ->delay(now()->addSeconds($delay * $index));
        }

        // Schedule next batch
        $nextDelay = $delay * $pendingRecipients->count() + 5;
        self::dispatch($this->campaign)
            ->delay(now()->addSeconds($nextDelay));
    }
}
