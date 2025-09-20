<!-- resources/views/activity/index.blade.php -->
<x-userlayout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-blue-600 rounded-lg shadow p-6 text-white">
            <h2 class="text-2xl font-semibold mb-1">Activity Logs</h2>
            <p class="text-blue-100">Track key actions performed in the system.</p>
        </div>

        {{-- Filters --}}
        {{-- Filters --}}
<form method="GET" class="bg-white rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end">
        {{-- Action --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
            <input type="text" name="action" value="{{ request('action') }}"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-blue-400"
                   placeholder="e.g. title.created">
        </div>

        @if ($isAdmin)
            {{-- Who --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Who</label>
                <input type="text" name="who" value="{{ request('who') }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-blue-400"
                       placeholder="Name">
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-blue-400">
                    <option value="">All</option>
                    <option value="ADMIN" @selected(request('role')==='ADMIN')>Admin</option>
                    <option value="ADVISER" @selected(request('role')==='ADVISER')>Adviser</option>
                    <option value="STUDENT" @selected(request('role')==='STUDENT')>Student</option>
                </select>
            </div>
        @endif

        {{-- From --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-blue-400">
        </div>

        {{-- To --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-blue-400">
        </div>

        {{-- Per-page --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Per page</label>
            <select name="per_page"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-blue-400">
                @foreach([10,20,50,100] as $opt)
                    <option value="{{ $opt }}" @selected(($perPage ?? 20)===$opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Filter</button>
            <a href="{{ url()->current() }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100">
                Reset
            </a>
        </div>
    </div>
</form>


        {{-- Table --}}
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">When</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>

                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @forelse ($logs as $log)
                    @php
                        $labels = [
                            'auth.login' => 'Logged in',
                            'auth.logout' => 'Logged out',
                            'title.created' => 'Title created',
                            'title.status_changed' => 'Title status changed',
                            'title.adviser_assigned' => 'Adviser assigned',
                            'title.final_document_set' => 'Final document set',
                            'title.deleted' => 'Title deleted',
                            'announcement.created' => 'Announcement created',
                            'announcement.updated' => 'Announcement updated',
                            'announcement.deleted' => 'Announcement deleted',
                            'paper.uploaded' => 'Research paper uploaded',
                            'paper.deleted' => 'Research paper deleted',
                            'document.created' => 'Document created',
                            'document.plagiarism_updated' => 'Plagiarism updated',
                            'adviser_request.created' => 'Adviser request created',
                            'adviser_request.status_changed' => 'Adviser request status changed',
                        ];
                        $friendly = $labels[$log->action] ?? Str::headline(str_replace('.', ' ', $log->action));
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800">
                            {{ $friendly }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800">
                            {{ $log->user?->name ?? '—' }} <sup class="text-gray-400 ms-1"> {{ $log->role ? "$log->role" : '' }}</sup>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800">
                            @if($log->subject)
                                {{ class_basename($log->subject_type) }}#{{ $log->subject_id }}
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                             <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                            {{ $log->ip ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $meta = $log->meta ?? []; @endphp
                            @if(!empty($meta))
                                <button type="button"
                                        onclick="openDetailsModal({{ json_encode($meta) }})"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </button>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                   
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500">No activity found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between mt-3">
            <div class="text-xs text-gray-500">
                Showing <span class="font-medium">{{ $logs->firstItem() }}</span>
                to <span class="font-medium">{{ $logs->lastItem() }}</span>
                of <span class="font-medium">{{ $logs->total() }}</span> results
            </div>
            <div>{{ $logs->onEachSide(1)->links() }}</div>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="hidden fixed inset-0 z-50">
        <div id="detailsOverlay" class="absolute inset-0 bg-black/50"></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Log Details</h3>
                <div id="detailsContent" class="space-y-2 text-sm text-gray-700"></div>
                <div class="mt-5 text-right">
                    <button onclick="closeDetailsModal()" type="button"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDetailsModal(meta) {
            const modal = document.getElementById('detailsModal');
            const content = document.getElementById('detailsContent');
            content.innerHTML = '';
            for (const [key, value] of Object.entries(meta)) {
                const val = (typeof value === 'object') ? JSON.stringify(value) : value;
                content.insertAdjacentHTML('beforeend',
                    `<div><span class="font-medium">${key}:</span> ${val}</div>`
                );
            }
            modal.classList.remove('hidden');
        }
        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }
        document.getElementById('detailsOverlay')?.addEventListener('click', closeDetailsModal);
    </script>
</x-userlayout>
