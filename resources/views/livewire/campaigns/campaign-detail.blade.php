<div wire:poll.3s>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $campaign->name }}</h2>
                @php
                    $colors = [
                        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                        'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                        'processing' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                        'paused' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                        'completed' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400',
                        'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                    ];
                @endphp
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $colors[$campaign->status] ?? $colors['draft'] }}">{{ ucfirst($campaign->status) }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $campaign->subject }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($campaign->status === 'processing')
                <button wire:click="pauseCampaign" class="px-4 py-2 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-sm font-medium rounded-xl hover:bg-amber-200 dark:hover:bg-amber-900/50 transition">Pause</button>
                <button wire:click="stopCampaign" wire:confirm="Emergency stop this campaign?" class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-sm font-medium rounded-xl hover:bg-red-200 dark:hover:bg-red-900/50 transition">🛑 Stop</button>
            @endif
            @if($campaign->status === 'paused')
                <button wire:click="resumeCampaign" class="px-4 py-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium rounded-xl hover:bg-emerald-200 transition">▶ Resume</button>
            @endif
            <a href="{{ route('campaigns.index') }}" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition" wire:navigate>← Back</a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['sent'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Sent</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Failed</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['pending'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Pending</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-violet-600">{{ $stats['success_rate'] }}%</p>
            <p class="text-xs text-gray-500 mt-1">Success Rate</p>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
            <span class="text-sm text-gray-500">{{ $stats['progress'] }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-3 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-600 transition-all duration-700" style="width: {{ $stats['progress'] }}%"></div>
        </div>
    </div>

    {{-- Actions + Recipients --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="p-5 border-b border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="font-semibold text-gray-900 dark:text-white">Recipients</h3>
            <div class="flex items-center gap-2">
                <select wire:model.live="recipientFilter" class="px-3 py-1.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-xs">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="queued">Queued</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                </select>
                @if($stats['failed'] > 0)
                    <button wire:click="retryFailed" class="px-3 py-1.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-medium rounded-lg hover:bg-amber-200 transition">Retry Failed</button>
                @endif
                <button wire:click="exportReport" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">Export CSV</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sent At</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($recipients as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-5 py-3 text-sm text-gray-900 dark:text-white">{{ $r->email }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $r->name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $sc = ['pending' => 'text-gray-500', 'queued' => 'text-blue-500', 'sent' => 'text-emerald-500', 'failed' => 'text-red-500'];
                                @endphp
                                <span class="text-xs font-medium {{ $sc[$r->status] ?? 'text-gray-500' }}">{{ ucfirst($r->status) }}</span>
                                @if($r->retry_count > 0)
                                    <span class="text-xs text-gray-400">(retry: {{ $r->retry_count }})</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $r->sent_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="px-5 py-3 text-xs text-red-500 max-w-xs truncate">{{ $r->error_message ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">No recipients</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recipients->hasPages())
            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-800">{{ $recipients->links() }}</div>
        @endif
    </div>
</div>
