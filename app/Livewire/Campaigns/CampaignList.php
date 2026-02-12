<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Services\CampaignService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Campaigns'])]
#[Title('Campaigns - Bulk Email Sender')]
class CampaignList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteCampaign(int $id): void
    {
        $campaign = Campaign::where('user_id', auth()->id())->findOrFail($id);
        $campaign->delete();
        $this->dispatch('toast', type: 'success', message: 'Campaign deleted successfully.');
    }

    public function duplicateCampaign(int $id): void
    {
        $campaign = Campaign::where('user_id', auth()->id())->findOrFail($id);
        $service = app(CampaignService::class);
        $newCampaign = $service->duplicateCampaign($campaign);
        $this->dispatch('toast', type: 'success', message: "Campaign duplicated: {$newCampaign->name}");
    }

    public function pauseCampaign(int $id): void
    {
        $campaign = Campaign::where('user_id', auth()->id())->findOrFail($id);
        app(CampaignService::class)->pauseCampaign($campaign);
        $this->dispatch('toast', type: 'info', message: 'Campaign paused.');
    }

    public function resumeCampaign(int $id): void
    {
        $campaign = Campaign::where('user_id', auth()->id())->findOrFail($id);
        app(CampaignService::class)->resumeCampaign($campaign);
        $this->dispatch('toast', type: 'success', message: 'Campaign resumed.');
    }

    public function stopCampaign(int $id): void
    {
        $campaign = Campaign::where('user_id', auth()->id())->findOrFail($id);
        app(CampaignService::class)->stopCampaign($campaign);
        $this->dispatch('toast', type: 'warning', message: 'Campaign stopped.');
    }

    public function render()
    {
        $campaigns = Campaign::where('user_id', auth()->id())
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->withCount([
                'recipients',
                'recipients as sent_count' => fn($q) => $q->where('status', 'sent'),
                'recipients as failed_count' => fn($q) => $q->where('status', 'failed'),
            ])
            ->latest()
            ->paginate(10);

        return view('livewire.campaigns.campaign-list', compact('campaigns'));
    }
}
