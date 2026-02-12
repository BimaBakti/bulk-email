<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\Recipient;
use App\Services\CampaignService;
use App\Services\EmailService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Campaign Detail'])]
#[Title('Campaign Detail - Bulk Email Sender')]
class CampaignDetail extends Component
{
    use WithPagination;

    public Campaign $campaign;
    public string $recipientFilter = '';
    public array $stats = [];

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $this->stats = app(CampaignService::class)->getStatistics($this->campaign);
    }

    public function pauseCampaign(): void
    {
        app(CampaignService::class)->pauseCampaign($this->campaign);
        $this->campaign->refresh();
        flash()->info('Campaign paused.');
    }

    public function resumeCampaign(): void
    {
        app(CampaignService::class)->resumeCampaign($this->campaign);
        $this->campaign->refresh();
        flash()->success('Campaign resumed.');
    }

    public function stopCampaign(): void
    {
        app(CampaignService::class)->stopCampaign($this->campaign);
        $this->campaign->refresh();
        $this->refreshStats();
        flash()->warning('Campaign stopped.');
    }

    public function retryFailed(): void
    {
        $this->campaign->recipients()
            ->where('status', 'failed')
            ->where('retry_count', '<', config('bulkemail.max_retry_attempts', 3))
            ->update(['status' => 'pending', 'error_message' => null]);

        if ($this->campaign->status !== Campaign::STATUS_PROCESSING) {
            app(CampaignService::class)->resumeCampaign($this->campaign);
        }

        $this->refreshStats();
        flash()->success('Failed emails queued for retry.');
    }

    public function exportReport()
    {
        // Simple CSV export
        $recipients = $this->campaign->recipients()->get();
        $csv = "Email,Name,Status,Sent At,Error\n";
        foreach ($recipients as $r) {
            $csv .= "\"{$r->email}\",\"{$r->name}\",\"{$r->status}\",\"{$r->sent_at}\",\"" . str_replace('"', '""', $r->error_message ?? '') . "\"\n";
        }

        $filename = "campaign-{$this->campaign->id}-report.csv";
        $path = storage_path("app/exports/{$filename}");
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        file_put_contents($path, $csv);

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    public function render()
    {
        $recipients = $this->campaign->recipients()
            ->when($this->recipientFilter, fn($q) => $q->where('status', $this->recipientFilter))
            ->latest()
            ->paginate(15);

        $this->refreshStats();

        return view('livewire.campaigns.campaign-detail', compact('recipients'));
    }
}
