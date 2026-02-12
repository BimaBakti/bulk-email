<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Recipient;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // 60 seconds between retries

    public function __construct(
        public Recipient $recipient,
        public Campaign $campaign
    ) {}

    public function handle(EmailService $emailService): void
    {
        // Re-check campaign status
        $campaign = $this->campaign->fresh();
        if ($campaign->status !== Campaign::STATUS_PROCESSING) {
            $this->recipient->update(['status' => Recipient::STATUS_PENDING]);
            return;
        }

        $emailService->sendEmail($this->recipient, $campaign);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendBulkEmailJob failed permanently", [
            'recipient_id' => $this->recipient->id,
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);

        $this->recipient->update([
            'status' => Recipient::STATUS_FAILED,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
