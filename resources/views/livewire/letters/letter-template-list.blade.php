<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Letter Templates (PDF)</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage PDF templates for dynamic attachments</p>
        </div>
        <a href="{{ route('letters.create') }}" class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium rounded-lg transition" wire:navigate>
            + New Letter Template
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <div class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition" placeholder="Search templates...">
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $template)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 flex flex-col group hover:border-violet-300 dark:hover:border-violet-700 transition relative">
                 <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-rose-50 dark:bg-rose-900/20 rounded-xl">
                        <svg class="w-8 h-8 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </button>
                        <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-10" style="display: none;">
                            <button wire:click="deleteTemplate({{ $template->id }})" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">Delete</button>
                        </div>
                    </div>
                </div>

                <a href="{{ route('letters.edit', $template->id) }}" class="flex-1" wire:navigate>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-violet-600 transition">{{ $template->name }}</h3>
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mt-2">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ ucfirst($template->page_size) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ ucfirst($template->orientation) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">Updated {{ $template->updated_at->diffForHumans() }}</p>
                </a>
            </div>
        @empty
            <div class="col-span-full border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No letter templates yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Create PDF templates to attach personalized letters to your emails.</p>
                <a href="{{ route('letters.create') }}" class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium rounded-lg transition" wire:navigate>Create Template</a>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $templates->links() }}
    </div>
</div>
