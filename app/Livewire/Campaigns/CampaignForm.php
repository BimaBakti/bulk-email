<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Attachment;
use App\Services\RecipientService;
use App\Services\CampaignService;
use App\Services\EmailService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Campaign'])]
#[Title('Campaign - Bulk Email Sender')]
class CampaignForm extends Component
{
    use WithFileUploads;

    public ?Campaign $campaign = null;
    public string $name = '';
    public string $subject = '';
    public string $body = '';
    public string $scheduleType = 'now'; // now, scheduled
    public ?string $scheduled_at = null;

    // Recipients
    public $recipientFile = null;
    public array $importResult = [];
    public int $recipientCount = 0;
    public array $availableTags = ['nama', 'email'];

    // Attachments
    public $attachmentFiles = [];
    public array $existingAttachments = [];

    // Template
    public ?int $selectedTemplateId = null;

    // Preview
    public bool $showPreview = false;
    public string $previewSubject = '';
    public string $previewBody = '';

    // Test Email
    public bool $showTestModal = false;
    public string $testEmailAddress = '';

    public function mount(?Campaign $campaign = null): void
    {
        if ($campaign && $campaign->exists) {
            $this->campaign = $campaign;
            $this->name = $campaign->name;
            $this->subject = $campaign->subject;
            $this->body = $campaign->body;
            $this->recipientCount = $campaign->recipients()->count();
            $this->existingAttachments = $campaign->attachments->toArray();

            if ($campaign->scheduled_at) {
                $this->scheduleType = 'scheduled';
                $this->scheduled_at = $campaign->scheduled_at->format('Y-m-d\TH:i');
            }

            $recipientService = app(RecipientService::class);
            $this->availableTags = $recipientService->getAvailableMergeTags($campaign);
        }
    }

    public function uploadRecipients(): void
    {
        $this->validate(['recipientFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240']);

        // Create campaign first if doesn't exist
        $campaign = $this->getOrCreateCampaign();

        $service = app(RecipientService::class);
        $this->importResult = $service->importFromFile($this->recipientFile, $campaign);
        $this->recipientCount = $campaign->recipients()->count();
        $this->availableTags = $service->getAvailableMergeTags($campaign);
        $this->recipientFile = null;

        $this->importResult['success'] ? flash()->success($this->importResult['message']) : flash()->error($this->importResult['message']);
    }

    public function uploadAttachments(): void
    {
        $this->validate(['attachmentFiles.*' => 'file|max:25600']); // 25MB max per file

        $campaign = $this->getOrCreateCampaign();

        foreach ($this->attachmentFiles as $file) {
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('attachments/' . $campaign->id, $filename);

            // Safely get file metadata
            try {
                $size = $file->getSize();
                $mimeType = $file->getMimeType();
            } catch (\Exception $e) {
                // Fallback: get from stored file
                $size = \Storage::size($path);
                $mimeType = \Storage::mimeType($path);
            }

            Attachment::create([
                'campaign_id' => $campaign->id,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $mimeType,
                'size' => $size,
            ]);
        }

        $this->existingAttachments = $campaign->attachments->fresh()->toArray();
        $this->attachmentFiles = [];
        flash()->success('Attachments uploaded.');
    }

    public function removeAttachment(int $id): void
    {
        $attachment = Attachment::find($id);
        if ($attachment) {
            \Storage::delete($attachment->path);
            $attachment->delete();
            $this->existingAttachments = array_filter($this->existingAttachments, fn($a) => $a['id'] !== $id);
        }
    }

    public function loadTemplate(): void
    {
        if (!$this->selectedTemplateId) return;
        $template = EmailTemplate::find($this->selectedTemplateId);
        if ($template) {
            $this->subject = $template->subject;
            $this->body = $template->body;
            flash()->info("Template '{$template->name}' loaded.");
            $this->dispatch('set-editor-content', content: $this->body);
        }
    }

    public function previewEmail(): void
    {
        $this->previewSubject = $this->subject;
        $this->previewBody = $this->body;

        // Replace tags with sample data
        $sampleReplacements = ['{{nama}}' => 'John Doe', '{{email}}' => 'john@example.com', '{{name}}' => 'John Doe'];
        foreach ($sampleReplacements as $tag => $val) {
            $this->previewSubject = str_replace($tag, $val, $this->previewSubject);
            $this->previewBody = str_replace($tag, $val, $this->previewBody);
        }

        $this->showPreview = true;
    }

    public function sendTestEmail(): void
    {
        $this->validate(['testEmailAddress' => 'required|email']);

        try {
            $service = app(EmailService::class);
            $subject = str_replace(['{{nama}}', '{{email}}'], ['Test User', $this->testEmailAddress], $this->subject);
            $body = str_replace(['{{nama}}', '{{email}}'], ['Test User', $this->testEmailAddress], $this->body);
            $service->sendTestEmail($this->testEmailAddress, $subject, $body);
            $this->showTestModal = false;
            flash()->success('Test email sent! (not counted in quota)');
        } catch (\Exception $e) {
            flash()->error('Test email failed: ' . $e->getMessage());
        }
    }

    public function saveDraft(): void
    {
        $this->saveCampaign('draft');
        flash()->success('Campaign saved as draft.');
    }

    public function startCampaign(): void
    {
        $campaign = $this->saveCampaign($this->scheduleType === 'scheduled' ? 'scheduled' : 'draft');

        if ($campaign->recipients()->count() === 0) {
            flash()->error('Please upload recipients first.');
            return;
        }

        $service = app(CampaignService::class);

        if ($this->scheduleType === 'now') {
            $service->startCampaign($campaign);
            flash()->success('Campaign started! Emails are being queued.');
        } else {
            $campaign->update(['status' => 'scheduled']);
            flash()->success('Campaign scheduled for ' . $campaign->scheduled_at->format('d M Y H:i'));
        }

        $this->redirect(route('campaigns.show', $campaign), navigate: true);
    }

    protected function saveCampaign(string $status = 'draft'): Campaign
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'name' => $this->name,
            'subject' => $this->subject,
            'body' => $this->body,
            'status' => $status,
            'scheduled_at' => $this->scheduleType === 'scheduled' ? $this->scheduled_at : null,
            'settings' => [
                'delay' => config('bulkemail.delay_between_emails'),
                'batch_size' => config('bulkemail.batch_size'),
            ],
        ];

        if ($this->campaign) {
            $this->campaign->update($data);
            return $this->campaign;
        } else {
            $this->campaign = Campaign::create($data);
            return $this->campaign;
        }
    }

    protected function getOrCreateCampaign(): Campaign
    {
        if ($this->campaign) return $this->campaign;
        return $this->saveCampaign('draft');
    }

    public function render()
    {
        $templates = EmailTemplate::where('user_id', auth()->id())->get();

        return view('livewire.campaigns.campaign-form', compact('templates'));
    }
}
