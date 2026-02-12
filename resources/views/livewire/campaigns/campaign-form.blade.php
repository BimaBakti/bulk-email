<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $campaign ? 'Edit Campaign' : 'New Campaign' }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Compose and send your bulk email campaign</p>
        </div>
        <a href="{{ route('campaigns.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition" wire:navigate>← Back to Campaigns</a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="xl:col-span-2 space-y-6">
            {{-- Campaign Info --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Campaign Details</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campaign Name</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition" placeholder="e.g. Newsletter February 2025">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Subject Line
                            <span class="text-gray-400 font-normal">— use merge tags: @foreach($availableTags as $tag)@php $tagStr = '{{' . $tag . '}}'; @endphp<code class="px-1 py-0.5 bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400 rounded text-xs mx-0.5 cursor-pointer" onclick="document.getElementById('subject-input').value += '{{ $tagStr }}'; document.getElementById('subject-input').dispatchEvent(new Event('input'));">{{ $tagStr }}</code>@endforeach</span>
                        </label>
                        <input type="text" id="subject-input" wire:model="subject" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition" placeholder="e.g. Halo @{{nama}}, ini update terbaru!">
                        @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Email Body (Quill) --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Email Body</h3>
                    <div class="flex gap-2">
                        @foreach($availableTags as $tag)
                            @php $mergeTag = '{{' . $tag . '}}'; @endphp
                            <button type="button" onclick="insertMergeTag('{{ $mergeTag }}')"
                                    class="px-2 py-1 text-xs bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400 rounded-lg hover:bg-violet-200 dark:hover:bg-violet-900/60 transition">{{ $mergeTag }}</button>
                        @endforeach
                    </div>
                </div>
                <div wire:ignore>
                    <div id="quill-editor" class="min-h-[300px] bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">{!! $body !!}</div>
                </div>
                <input type="hidden" id="body-hidden" wire:model="body">
                @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Recipients Upload --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">
                    Recipients
                    <span class="text-sm font-normal text-gray-500 ml-2">{{ number_format($recipientCount) }} recipients</span>
                </h3>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-6 text-center">
                    <input type="file" wire:model="recipientFile" class="hidden" id="recipient-file" accept=".csv,.xlsx,.xls">
                    <label for="recipient-file" class="cursor-pointer">
                        <svg class="w-10 h-10 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Drop CSV/Excel file or <span class="text-violet-600 dark:text-violet-400 font-medium">browse</span></p>
                        <p class="text-xs text-gray-400 mt-1">Required columns: email, nama/name. Additional columns become merge tags.</p>
                    </label>
                    <div wire:loading wire:target="recipientFile" class="mt-3">
                        <svg class="w-5 h-5 mx-auto animate-spin text-violet-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </div>
                @if($recipientFile)
                    <div class="mt-3 flex items-center gap-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $recipientFile->getClientOriginalName() }}</span>
                        <button wire:click="uploadRecipients" class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium rounded-lg transition">Import</button>
                    </div>
                @endif
                @if(!empty($importResult))
                    <div class="mt-3 p-3 rounded-xl {{ $importResult['success'] ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' }}">
                        <p class="text-sm font-medium">{{ $importResult['message'] }}</p>
                        @if(!empty($importResult['errors']))
                            <ul class="text-xs mt-2 space-y-1">
                                @foreach(array_slice($importResult['errors'], 0, 5) as $err)
                                    <li>• {{ $err }}</li>
                                @endforeach
                                @if(count($importResult['errors']) > 5)
                                    <li class="text-gray-500">... and {{ count($importResult['errors']) - 5 }} more errors</li>
                                @endif
                            </ul>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Attachments --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Attachments</h3>
                <div class="flex items-center gap-3">
                    <input type="file" wire:model="attachmentFiles" multiple class="hidden" id="attachment-files">
                    <label for="attachment-files" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Add Files
                    </label>
                    @if(!empty($attachmentFiles))
                        <button wire:click="uploadAttachments" class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium rounded-lg transition">Upload</button>
                    @endif
                </div>
                @if(!empty($existingAttachments))
                    <div class="mt-3 space-y-2">
                        @foreach($existingAttachments as $att)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $att['original_name'] }}</span>
                                </div>
                                <button wire:click="removeAttachment({{ $att['id'] }})" class="text-red-500 hover:text-red-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Template Selector --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Load Template</h3>
                <div class="flex gap-2">
                    <select wire:model="selectedTemplateId" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm">
                        <option value="">Select template...</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <button wire:click="loadTemplate" class="px-3 py-2 bg-violet-600 hover:bg-violet-500 text-white text-sm rounded-lg transition">Load</button>
                </div>
            </div>

            {{-- Schedule --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Schedule</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model="scheduleType" value="now" class="text-violet-600 focus:ring-violet-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Send Now</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model="scheduleType" value="scheduled" class="text-violet-600 focus:ring-violet-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Schedule</span>
                    </label>
                    @if($scheduleType === 'scheduled')
                        <input type="datetime-local" wire:model="scheduled_at" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm">
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 space-y-3">
                <button wire:click="previewEmail" class="w-full py-2.5 px-4 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium text-sm rounded-xl transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Preview
                </button>
                <button wire:click="$set('showTestModal', true)" class="w-full py-2.5 px-4 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-blue-700 dark:text-blue-400 font-medium text-sm rounded-xl transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Send Test Email
                </button>
                <button wire:click="saveDraft" class="w-full py-2.5 px-4 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium text-sm rounded-xl transition">
                    Save Draft
                </button>
                <button wire:click="startCampaign"
                        class="w-full py-3 px-4 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-violet-500/25 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    {{ $scheduleType === 'now' ? 'Start Campaign' : 'Schedule Campaign' }}
                </button>
            </div>

            {{-- Info --}}
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5">
                <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-400 mb-2">📌 Tips</h4>
                <ul class="text-xs text-amber-700 dark:text-amber-400 space-y-1.5">
                    <li>• CSV harus memiliki header: <code>email, nama</code></li>
                    <li>• Kolom tambahan otomatis menjadi merge tags</li>
                    <li>• Gunakan <code>@{{nama}}</code> di subject/body untuk personalisasi</li>
                    <li>• Max {{ config('bulkemail.daily_limit') }} email per hari</li>
                    <li>• Delay {{ config('bulkemail.delay_between_emails') }} detik antar email</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Preview Modal --}}
    @if($showPreview)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="$wire.set('showPreview', false)">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Email Preview</h3>
                    <button wire:click="$set('showPreview', false)" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Subject:</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $previewSubject }}</p>
                    </div>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-6 bg-gray-50 dark:bg-gray-800">
                        <div class="prose dark:prose-invert max-w-none text-sm">{!! $previewBody !!}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Test Email Modal --}}
    @if($showTestModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="$wire.set('showTestModal', false)">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-2xl max-w-md w-full p-6">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">Send Test Email</h3>
                <p class="text-sm text-gray-500 mb-4">This won't count towards your daily quota.</p>
                <input type="email" wire:model="testEmailAddress" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm mb-4" placeholder="test@example.com">
                @error('testEmailAddress') <p class="text-red-500 text-xs mb-4">{{ $message }}</p> @enderror
                <div class="flex gap-3">
                    <button wire:click="$set('showTestModal', false)" class="flex-1 py-2.5 px-4 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">Cancel</button>
                    <button wire:click="sendTestEmail" class="flex-1 py-2.5 px-4 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition">
                        <span wire:loading.remove wire:target="sendTestEmail">Send Test</span>
                        <span wire:loading wire:target="sendTestEmail">Sending...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Quill Editor Scripts --}}
    @push('scripts')
    @endpush
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('#quill-editor')) {
                const quill = new Quill('#quill-editor', {
                    theme: 'snow',
                    placeholder: 'Compose your email...',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'align': [] }],
                            ['link', 'image'],
                            ['clean']
                        ]
                    }
                });

                quill.on('text-change', function() {
                    @this.set('body', quill.root.innerHTML);
                });

                window.insertMergeTag = function(tag) {
                    const range = quill.getSelection(true);
                    quill.insertText(range.index, tag);
                };

                Livewire.on('set-editor-content', (data) => {
                    quill.root.innerHTML = data[0]?.content || data.content || '';
                });
            }
        });
    </script>
</div>
