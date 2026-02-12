<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Email Templates</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Save and reuse email templates</p>
        </div>
        <a href="{{ route('templates.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-violet-500/25 transition-all" wire:navigate>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Template
        </a>
    </div>

    <div class="mb-6">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search templates..."
               class="w-full max-w-md px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($templates as $template)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-lg transition-shadow group">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $template->name }}</h3>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                        <a href="{{ route('templates.edit', $template) }}" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-blue-600 transition" wire:navigate>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <button wire:click="deleteTemplate({{ $template->id }})" wire:confirm="Delete template '{{ $template->name }}'?" class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-gray-400 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Subject: {{ Str::limit($template->subject, 50) }}</p>
                <div class="text-xs text-gray-400 dark:text-gray-500 line-clamp-3 prose dark:prose-invert">{!! Str::limit(strip_tags($template->body), 120) !!}</div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400">
                    {{ $template->created_at->diffForHumans() }}
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/></svg>
                <p class="text-sm">No templates yet</p>
            </div>
        @endforelse
    </div>

    @if($templates->hasPages())
        <div class="mt-6">{{ $templates->links() }}</div>
    @endif
</div>
