<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $letterTemplate ? 'Edit Letter Template' : 'New Letter Template' }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Design your PDF letter layout</p>
        </div>
        <div class="flex gap-3">
             <button wire:click="previewPdf" class="px-4 py-2 border border-violet-200 dark:border-violet-800 text-violet-700 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/20 hover:bg-violet-100 dark:hover:bg-violet-900/40 text-sm font-medium rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview PDF
            </button>
            <a href="{{ route('letters.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition" wire:navigate>Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Editor --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template Name</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition" placeholder="e.g. Surat Undangan Resmi">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Letter Body</label>
                        <div class="flex gap-2">
                            @php $mergeTags = ['nama', 'email', 'alamat', 'jabatan', 'perusahaan']; @endphp
                            @foreach($mergeTags as $tag)
                                @php $tagString = '{{' . $tag . '}}'; @endphp
                                <button type="button" onclick="insertMergeTagLetter('{{ $tagString }}')"
                                        class="px-2 py-1 text-xs bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400 rounded-lg hover:bg-violet-200 transition">{{ $tagString }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div wire:ignore>
                        <div id="quill-editor-letter" class="min-h-[500px] bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">{!! $body !!}</div>
                    </div>
                    <input type="hidden" id="body-hidden-letter" wire:model="body">
                    @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Sidebar settings --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Page Settings</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Page Size</label>
                    <select wire:model="pageSize" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                        <option value="a4">A4</option>
                        <option value="letter">Letter</option>
                        <option value="legal">Legal</option>
                        <option value="f4">F4</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Orientation</label>
                    <select wire:model="orientation" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                        <option value="portrait">Portrait</option>
                        <option value="landscape">Landscape</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="save" class="w-full py-3 px-4 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-violet-500/25 transition-all">
                        <span wire:loading.remove wire:target="save">Save Template</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5">
                 <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-400 mb-2">ℹ️ Info PDF</h4>
                 <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
                     Template ini akan di-convert ke PDF menggunakan DomPDF. <br><br>
                     Pastikan merge tags yang Anda gunakan (misal <code>@{{alamat}}</code>) sesuai dengan header kolom di file Excel/CSV penerima nanti.
                 </p>
            </div>
        </div>
    </div>

    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('#quill-editor-letter')) {
                const quill = new Quill('#quill-editor-letter', {
                    theme: 'snow',
                    placeholder: 'Write your letter content...',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'align': [] }, { 'indent': '-1'}, { 'indent': '+1' }],
                            ['link', 'image'],
                            ['clean']
                        ]
                    }
                });

                quill.on('text-change', function() {
                    @this.set('body', quill.root.innerHTML);
                });

                window.insertMergeTagLetter = function(tag) {
                    const range = quill.getSelection(true);
                    quill.insertText(range.index, tag);
                };
            }
        });
    </script>
</div>
