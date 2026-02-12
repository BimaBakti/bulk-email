<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $emailTemplate ? 'Edit Template' : 'New Template' }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create reusable email templates with merge tags</p>
        </div>
        <a href="{{ route('templates.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition" wire:navigate>← Back</a>
    </div>

    <div class="max-w-3xl space-y-6">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template Name</label>
                <input type="text" wire:model="name" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition" placeholder="e.g. Welcome Email">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject Line</label>
                <input type="text" wire:model="subject" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition" placeholder="e.g. Halo {{nama}}, ini email untuk Anda!">
                @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Body</label>
                <div class="flex gap-2 mb-2">
                    @foreach(['nama', 'email'] as $tag)
                        <button type="button" onclick="insertMergeTagTpl('{{ '{{' . $tag . '}}' }}')"
                                class="px-2 py-1 text-xs bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400 rounded-lg hover:bg-violet-200 transition">{!! '{{' . $tag . '}}' !!}</button>
                    @endforeach
                </div>
                <div wire:ignore>
                    <div id="quill-editor-tpl" class="min-h-[250px] bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">{!! $body !!}</div>
                </div>
                <input type="hidden" id="body-hidden-tpl" wire:model="body">
                @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('templates.index') }}" class="px-6 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition" wire:navigate>Cancel</a>
            <button wire:click="save" class="px-6 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-violet-500/25 transition-all">
                <span wire:loading.remove wire:target="save">Save Template</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>

    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('#quill-editor-tpl')) {
                const quill = new Quill('#quill-editor-tpl', {
                    theme: 'snow',
                    placeholder: 'Compose your template...',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'align': [] }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                quill.on('text-change', function() {
                    @this.set('body', quill.root.innerHTML);
                });

                window.insertMergeTagTpl = function(tag) {
                    const range = quill.getSelection(true);
                    quill.insertText(range.index, tag);
                };
            }
        });
    </script>
</div>
