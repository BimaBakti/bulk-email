<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">SMTP configuration and rate limiting</p>
    </div>

    <div class="max-w-3xl space-y-6">
        {{-- SMTP Configuration --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Gmail SMTP Configuration
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Host</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ config('mail.mailers.smtp.host', '-') }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Port</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ config('mail.mailers.smtp.port', '-') }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Encryption</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ strtoupper(config('mail.mailers.smtp.encryption', '-')) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Username</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ config('mail.mailers.smtp.username') ? Str::mask(config('mail.mailers.smtp.username'), '*', 3, -10) : '-' }}</p>
                </div>
            </div>

            <div class="mt-4 flex gap-3">
                <button wire:click="testSmtpConnection" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition flex items-center gap-2">
                    <svg wire:loading.class="animate-spin" wire:target="testSmtpConnection" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Test SMTP Connection
                </button>
            </div>

            @if($smtpResult)
                <div class="mt-3 p-3 rounded-xl {{ $smtpResult['success'] ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' }}">
                    <p class="text-sm font-medium">{{ $smtpResult['message'] }}</p>
                </div>
            @endif
        </div>

        {{-- Rate Limiting --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Rate Limiting Configuration
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Daily Limit</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ config('bulkemail.daily_limit') }}</p>
                    <p class="text-xs text-gray-400">emails/day</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Hourly Limit</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ config('bulkemail.hourly_limit') }}</p>
                    <p class="text-xs text-gray-400">emails/hour</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Delay Between</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ config('bulkemail.delay_between_emails') }}s</p>
                    <p class="text-xs text-gray-400">seconds</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Max Retry</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ config('bulkemail.max_retry_attempts') }}x</p>
                    <p class="text-xs text-gray-400">attempts</p>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">* To change these values, update the <code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-violet-600 dark:text-violet-400">.env</code> file.</p>
        </div>

        {{-- Today's Quota --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Today's Usage</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Daily Quota</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $quotaStats['sent_today'] }} / {{ $quotaStats['limit'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-3">
                        <div class="h-full rounded-full transition-all {{ $quotaStats['percentage'] >= 90 ? 'bg-red-500' : ($quotaStats['percentage'] >= 80 ? 'bg-amber-500' : 'bg-violet-500') }}" style="width: {{ min($quotaStats['percentage'], 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Hourly Rate</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $quotaStats['hourly_sent'] }} / {{ $quotaStats['hourly_limit'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-3">
                        @php $hp = $quotaStats['hourly_limit'] > 0 ? ($quotaStats['hourly_sent'] / $quotaStats['hourly_limit']) * 100 : 0; @endphp
                        <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: {{ min($hp, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Test Email --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Send Test Email
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Send a test email to verify SMTP configuration. Not counted towards quota.</p>
            <div class="flex gap-3">
                <input type="email" wire:model="testEmailAddress" class="flex-1 px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition" placeholder="test@example.com">
                <button wire:click="sendTestEmail" class="px-5 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-violet-500/25 transition-all text-sm">
                    <span wire:loading.remove wire:target="sendTestEmail">Send Test</span>
                    <span wire:loading wire:target="sendTestEmail">Sending...</span>
                </button>
            </div>
            @error('testEmailAddress') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Gmail Setup Guide --}}
        <div class="bg-gradient-to-br from-violet-50 to-indigo-50 dark:from-violet-950/30 dark:to-indigo-950/30 rounded-2xl border border-violet-200 dark:border-violet-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                📖 Setup Gmail App Password
            </h3>
            <ol class="text-sm text-gray-700 dark:text-gray-300 space-y-2 list-decimal list-inside">
                <li>Buka <a href="https://myaccount.google.com/security" target="_blank" class="text-violet-600 dark:text-violet-400 hover:underline">Google Account Security</a></li>
                <li>Aktifkan <strong>2-Step Verification</strong> jika belum aktif</li>
                <li>Kembali ke Security → pilih <strong>App passwords</strong></li>
                <li>Pilih app: <strong>Mail</strong>, device: <strong>Other (Custom name)</strong> → ketik "BulkMailer"</li>
                <li>Klik <strong>Generate</strong> → copy password 16 karakter</li>
                <li>Paste ke <code class="px-1 py-0.5 bg-white dark:bg-gray-800 rounded text-violet-600 dark:text-violet-400">.env</code>:
                    <pre class="mt-1 bg-white dark:bg-gray-800 rounded-lg p-2 text-xs overflow-x-auto">MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password</pre>
                </li>
                <li>Jalankan <code class="px-1 py-0.5 bg-white dark:bg-gray-800 rounded text-violet-600 dark:text-violet-400">php artisan config:clear</code></li>
                <li>Test koneksi SMTP di atas ☝</li>
            </ol>
        </div>
    </div>
</div>
