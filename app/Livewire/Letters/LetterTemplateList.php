<?php

namespace App\Livewire\Letters;

use App\Models\LetterTemplate;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app', ['title' => 'Letter Templates'])]
#[Title('Letter Templates - Bulk Email Sender')]
class LetterTemplateList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteTemplate(int $id): void
    {
        LetterTemplate::where('user_id', auth()->id())->findOrFail($id)->delete();
        flash()->success('Letter template deleted.');
    }

    public function render()
    {
        $templates = LetterTemplate::where('user_id', auth()->id())
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(12);

        return view('livewire.letters.letter-template-list', compact('templates'));
    }
}
