{{-- Use Alpine.js for simple Tab switching inside the loaded content --}}
<div x-data="{ currentTab: 'history' }">
    
    {{-- Modal Header --}}
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-lg">
        <h3 class="font-bold text-lg text-slate-800 truncate pr-4">
            {{ $submission->title }}
        </h3>
        <button type="button" onclick="closeLogModal()" class="text-slate-400 hover:text-red-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    {{-- Tabs --}}
    <div class="px-6 pt-4 border-b border-slate-200 flex gap-6">
        <button @click="currentTab = 'history'" 
                :class="currentTab === 'history' ? 'border-primary-600 text-primary-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="pb-3 text-sm font-bold border-b-2 transition">
            History
        </button>
        {{-- <button @click="currentTab = 'notes'" 
                :class="currentTab === 'notes' ? 'border-primary-600 text-primary-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="pb-3 text-sm font-bold border-b-2 transition">
            Notes
        </button> --}}
    </div>

    {{-- Content --}}
    <div class="p-6 bg-white max-h-[60vh] overflow-y-auto">
        
        {{-- History Tab --}}
        <div x-show="currentTab === 'history'">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-2 w-32">Date</th>
                        <th class="px-4 py-2 w-40">User</th>
                        <th class="px-4 py-2">Event</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($submission->activityLogs as $log)
                        <tr>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700">{{ $log->user->name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $log->description ?? $log->title }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400 italic">No history.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Notes Tab --}}
        {{-- <div x-show="currentTab === 'notes'" style="display: none;">
            <div class="space-y-4">
                @forelse($submission->notes as $note)
                    <div class="bg-slate-50 p-4 rounded border border-slate-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold text-slate-700">{{ $note->user->name }}</span>
                            <span class="text-xs text-slate-400">{{ $note->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="text-sm text-slate-600 whitespace-pre-line">{!! $note->content !!}</div>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400 italic border-2 border-dashed border-slate-200 rounded">
                        No notes attached.
                    </div>
                @endforelse
            </div>
        </div> --}}

    </div>
</div>