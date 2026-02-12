<?php

namespace App\Livewire;

use App\Models\EmailLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Email Logs'])]
#[Title('Email Logs - Bulk Email Sender')]
class EmailLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function exportLogs()
    {
        $logs = EmailLog::with(['campaign', 'recipient'])
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->get();

        $csv = "Campaign,Email,Name,Status,Sent At,Error\n";
        foreach ($logs as $log) {
            $csv .= "\"{$log->campaign->name}\",\"{$log->recipient->email}\",\"{$log->recipient->name}\",\"{$log->status}\",\"{$log->sent_at}\",\"" . str_replace('"', '""', $log->error_message ?? '') . "\"\n";
        }

        $filename = "email-logs-" . now()->format('Y-m-d') . ".csv";
        $path = storage_path("app/exports/{$filename}");
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        file_put_contents($path, $csv);

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    public function render()
    {
        $logs = EmailLog::with(['campaign', 'recipient'])
            ->when($this->search, function ($q) {
                $q->whereHas('recipient', fn($r) => $r->where('email', 'like', "%{$this->search}%"))
                  ->orWhereHas('campaign', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(20);

        return view('livewire.email-logs', compact('logs'));
    }
}
