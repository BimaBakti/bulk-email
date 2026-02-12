<?php

namespace App\Livewire\Templates;

use App\Models\EmailTemplate;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Template'])]
#[Title('Template - Bulk Email Sender')]
class TemplateForm extends Component
{
    public ?EmailTemplate $emailTemplate = null;
    public string $name = '';
    public string $subject = '';
    public string $body = '';

    public function mount(?EmailTemplate $emailTemplate = null): void
    {
        if ($emailTemplate && $emailTemplate->exists) {
            $this->emailTemplate = $emailTemplate;
            $this->name = $emailTemplate->name;
            $this->subject = $emailTemplate->subject;
            $this->body = $emailTemplate->body;
        }
    }

    public function save(): void
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
        ];

        if ($this->emailTemplate) {
            $this->emailTemplate->update($data);
        } else {
            EmailTemplate::create($data);
        }

        $this->dispatch('toast', type: 'success', message: 'Template saved!');
        $this->redirect(route('templates.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.templates.template-form');
    }
}
