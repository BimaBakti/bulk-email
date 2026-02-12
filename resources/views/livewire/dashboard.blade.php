<div wire:poll.5s>
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Sent Today --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400">Hari Ini</span>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['sent_today']) }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Email Terkirim</p>
        </div>

        {{-- Remaining Quota --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                @if($stats['is_near_limit'])
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 animate-pulse">⚠ Near Limit</span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['remaining']) }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sisa Quota ({{ $stats['limit'] }}/hari)</p>
        </div>

        {{-- Hourly Rate --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['hourly_sent'] }}<span class="text-lg text-gray-400">/{{ $stats['hourly_limit'] }}</span></p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Rate Per Jam</p>
        </div>

        {{-- Total Campaigns --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalCampaigns) }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Campaigns</p>
        </div>
    </div>

    {{-- Daily Quota Progress Bar --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 mb-8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-900 dark:text-white">Daily Quota Usage</h3>
            <span class="text-sm text-gray-500">{{ $stats['sent_today'] }} / {{ $stats['limit'] }}</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-4 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 {{ $stats['percentage'] >= 90 ? 'bg-gradient-to-r from-red-500 to-red-600' : ($stats['percentage'] >= 80 ? 'bg-gradient-to-r from-amber-500 to-orange-500' : 'bg-gradient-to-r from-violet-500 to-indigo-600') }}"
                 style="width: {{ min($stats['percentage'], 100) }}%"></div>
        </div>
        @if($stats['is_near_limit'])
            <p class="text-sm text-amber-600 dark:text-amber-400 mt-2 flex items-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                Mendekati batas harian! Sisa {{ $stats['remaining'] }} email.
            </p>
        @endif
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        {{-- Active Campaigns --}}
        <div class="xl:col-span-1">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    Active Campaigns
                </h3>
                @forelse($activeCampaigns as $ac)
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 mb-3">
                        <div class="flex justify-between items-start mb-2">
                            <a href="{{ route('campaigns.show', $ac) }}" class="font-medium text-sm text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 transition">{{ $ac->name }}</a>
                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Processing</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2">
                            @php $progress = $ac->recipients_count > 0 ? round(($ac->sent_count / $ac->recipients_count) * 100, 1) : 0; @endphp
                            <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-600 transition-all duration-500" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ $ac->sent_count }}/{{ $ac->recipients_count }} sent</span>
                            <span>{{ $progress }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada campaign aktif</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Campaigns --}}
        <div class="xl:col-span-2">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Recent Campaigns</h3>
                    <a href="{{ route('campaigns.create') }}" class="text-sm text-violet-600 dark:text-violet-400 hover:text-violet-500 font-medium">+ New Campaign</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campaign</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progress</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($recentCampaigns as $campaign)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('campaigns.show', $campaign) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-violet-600 transition">{{ $campaign->name }}</a>
                                    </td>
                                    <td class="px-5 py-3">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                                'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                                'processing' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                                                'paused' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                                                'completed' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400',
                                                'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$campaign->status] ?? $statusColors['draft'] }}">
                                            {{ ucfirst($campaign->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                                @php $p = $campaign->recipients_count > 0 ? round(($campaign->sent_count / $campaign->recipients_count) * 100) : 0; @endphp
                                                <div class="h-full rounded-full bg-violet-500" style="width: {{ $p }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $campaign->sent_count }}/{{ $campaign->recipients_count }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-xs text-gray-500">{{ $campaign->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <p class="text-sm">Belum ada campaign. <a href="{{ route('campaigns.create') }}" class="text-violet-600 dark:text-violet-400 font-medium">Buat sekarang!</a></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
