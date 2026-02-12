<?php

namespace App\Livewire\Letters;

use App\Models\LetterTemplate;
use App\Models\Recipient;
use App\Services\PdfGeneratorService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app', ['title' => 'Letter Template'])]
#[Title('Letter Template - Bulk Email Sender')]
class LetterTemplateForm extends Component
{
    public ?LetterTemplate $letterTemplate = null;
    public string $name = '';
    public string $body = '';
    public string $pageSize = 'a4';
    public string $orientation = 'portrait';

    public function mount(?LetterTemplate $letterTemplate = null): void
    {
        if ($letterTemplate && $letterTemplate->exists) {
            $this->letterTemplate = $letterTemplate;
            $this->name = $letterTemplate->name;
            $this->body = $letterTemplate->body;
            $this->pageSize = $letterTemplate->page_size;
            $this->orientation = $letterTemplate->orientation;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string',
            'pageSize' => 'required|string|in:a4,letter,legal,f4',
            'orientation' => 'required|string|in:portrait,landscape',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'name' => $this->name,
            'body' => $this->body,
            'page_size' => $this->pageSize,
            'orientation' => $this->orientation,
        ];

        if ($this->letterTemplate) {
            $this->letterTemplate->update($data);
        } else {
            LetterTemplate::create($data);
        }

        flash()->success('Letter template saved!');
        $this->redirect(route('letters.index'), navigate: true);
    }

    public function previewPdf()
    {
        $this->validate([
            'body' => 'required|string',
        ]);

        try {
            // Create a dummy template object (not saved)
            $template = new LetterTemplate([
                'body' => $this->body,
                'page_size' => $this->pageSize,
                'orientation' => $this->orientation,
            ]);

            // Create dummy recipient
            $recipient = new Recipient([
                'email' => 'test@example.com',
                'name' => 'John Doe',
                'custom_fields' => [
                    'alamat' => 'Jl. Sudirman No. 123, Jakarta',
                    'jabatan' => 'Manager Marketing',
                    'perusahaan' => 'PT Maju Mundur',
                ]
            ]);

            $service = app(PdfGeneratorService::class);
            $path = $service->generateForRecipient($template, $recipient, 'Preview');
            
            return response()->download(Storage::path($path), 'preview.pdf')->deleteFileAfterSend();

        } catch (\Exception $e) {
            flash()->error('Preview failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.letters.letter-template-form');
    }
}
