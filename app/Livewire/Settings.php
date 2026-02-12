<?php

namespace App\Livewire;

use App\Services\EmailService;
use App\Services\QuotaService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Settings'])]
#[Title('Settings - Bulk Email Sender')]
class Settings extends Component
{
    public string $testEmailAddress = '';
    public bool $smtpTesting = false;
    public ?array $smtpResult = null;

    public function testSmtpConnection(): void
    {
        $this->smtpTesting = true;
        $service = app(EmailService::class);
        $this->smtpResult = $service->testSmtpConnection();
        $this->smtpTesting = false;

        $this->smtpResult['success'] ? flash()->success($this->smtpResult['message']) : flash()->error($this->smtpResult['message']);
    }

    public function sendTestEmail(): void
    {
        $this->validate(['testEmailAddress' => 'required|email']);

        try {
            $service = app(EmailService::class);
            $service->sendTestEmail(
                $this->testEmailAddress,
                'Test Email dari BulkMailer',
                '<h2>Test Email Berhasil!</h2><p>Jika Anda menerima email ini, berarti konfigurasi Gmail SMTP Anda sudah benar.</p><p>Dikirim pada: ' . now()->format('d M Y H:i:s') . '</p>'
            );
            flash()->success('Test email sent to ' . $this->testEmailAddress);
        } catch (\Exception $e) {
            flash()->error('Failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $quotaStats = app(QuotaService::class)->getDailyStats();

        return view('livewire.settings', compact('quotaStats'));
    }
}
