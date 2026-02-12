<?php

namespace App\Services;

use App\Models\Campaign;
use App\Jobs\ProcessCampaignJob;
use Illuminate\Support\Facades\Auth;

class CampaignService
{
    /**
     * Start a campaign (dispatch processing job).
     */
    public function startCampaign(Campaign $campaign): void
    {
        $campaign->update([
            'status' => Campaign::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        ProcessCampaignJob::dispatch($campaign);
    }

    /**
     * Pause a running campaign.
     */
    public function pauseCampaign(Campaign $campaign): void
    {
        $campaign->update(['status' => Campaign::STATUS_PAUSED]);
    }

    /**
     * Resume a paused campaign.
     */
    public function resumeCampaign(Campaign $campaign): void
    {
        $campaign->update(['status' => Campaign::STATUS_PROCESSING]);
        ProcessCampaignJob::dispatch($campaign);
    }

    /**
     * Stop/cancel a campaign.
     */
    public function stopCampaign(Campaign $campaign): void
    {
        $campaign->update([
            'status' => Campaign::STATUS_FAILED,
            'completed_at' => now(),
        ]);

        // Mark all pending/queued recipients as pending again
        $campaign->recipients()
            ->where('status', 'queued')
            ->update(['status' => 'pending']);
    }

    /**
     * Duplicate a campaign.
     */
    public function duplicateCampaign(Campaign $campaign): Campaign
    {
        $newCampaign = $campaign->replicate();
        $newCampaign->name = $campaign->name . ' (Copy)';
        $newCampaign->status = Campaign::STATUS_DRAFT;
        $newCampaign->scheduled_at = null;
        $newCampaign->started_at = null;
        $newCampaign->completed_at = null;
        $newCampaign->save();

        // Duplicate recipients
        foreach ($campaign->recipients as $recipient) {
            $newRecipient = $recipient->replicate();
            $newRecipient->campaign_id = $newCampaign->id;
            $newRecipient->status = 'pending';
            $newRecipient->sent_at = null;
            $newRecipient->error_message = null;
            $newRecipient->retry_count = 0;
            $newRecipient->save();
        }

        return $newCampaign;
    }

    /**
     * Get campaign statistics.
     */
    public function getStatistics(Campaign $campaign): array
    {
        $total = $campaign->recipients()->count();
        $sent = $campaign->recipients()->where('status', 'sent')->count();
        $failed = $campaign->recipients()->where('status', 'failed')->count();
        $pending = $campaign->recipients()->whereIn('status', ['pending', 'queued'])->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 1) : 0,
            'progress' => $total > 0 ? round((($sent + $failed) / $total) * 100, 1) : 0,
        ];
    }
}
