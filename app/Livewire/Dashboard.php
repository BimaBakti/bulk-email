<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\DailyQuota;
use App\Models\EmailLog;
use App\Services\QuotaService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Dashboard'])]
#[Title('Dashboard - Bulk Email Sender')]
class Dashboard extends Component
{
    public function render()
    {
        $quotaService = app(QuotaService::class);
        $stats = $quotaService->getDailyStats();
        $user = auth()->user();

        $recentCampaigns = Campaign::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->withCount([
                'recipients',
                'recipients as sent_count' => fn($q) => $q->where('status', 'sent'),
                'recipients as failed_count' => fn($q) => $q->where('status', 'failed'),
            ])
            ->get();

        $activeCampaigns = Campaign::where('user_id', $user->id)
            ->where('status', Campaign::STATUS_PROCESSING)
            ->withCount([
                'recipients',
                'recipients as sent_count' => fn($q) => $q->where('status', 'sent'),
                'recipients as failed_count' => fn($q) => $q->where('status', 'failed'),
                'recipients as pending_count' => fn($q) => $q->whereIn('status', ['pending', 'queued']),
            ])
            ->get();

        $totalCampaigns = Campaign::where('user_id', $user->id)->count();
        $totalSentAllTime = EmailLog::where('status', 'sent')->count();

        return view('livewire.dashboard', [
            'stats' => $stats,
            'recentCampaigns' => $recentCampaigns,
            'activeCampaigns' => $activeCampaigns,
            'totalCampaigns' => $totalCampaigns,
            'totalSentAllTime' => $totalSentAllTime,
        ]);
    }
}
