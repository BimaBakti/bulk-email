<?php

namespace App\Livewire\Templates;

use App\Models\EmailTemplate;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Templates'])]
#[Title('Templates - Bulk Email Sender')]
class TemplateList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteTemplate(int $id): void
    {
        EmailTemplate::where('user_id', auth()->id())->findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Template deleted.');
    }

    public function render()
    {
        $templates = EmailTemplate::where('user_id', auth()->id())
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(12);

        return view('livewire.templates.template-list', compact('templates'));
    }
}
