<x-userlayout>
    <x-header.bar
        title="Submitted Titles"
        subtitle="Research titles successfully submitted to the system"
        :unread-count="$unreadCount ?? 0"
        :notifications="$notifications ?? collect()"
        :user="Auth::user()"
    />

    <div class="container mx-auto px-4 mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- 🔍 Search + Filter -->
        <form method="GET" class="flex flex-wrap items-center gap-4 mb-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full sm:w-auto flex-1 border border-gray-300 rounded px-4 py-2 text-sm"
                   placeholder="Search by title...">
            <button type="submit"
                    class="px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                Search
            </button>
            <select name="status" onchange="this.form.submit()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
            </select>
        </form>

        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $titles->firstItem() ?? 0 }}</span>–
                <span class="font-medium">{{ $titles->lastItem() ?? 0 }}</span>
                of <span class="font-medium">{{ $titles->total() }}</span>
            </p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($titles->isEmpty())
                <div class="text-center py-12">
                    <h4 class="text-xl font-semibold mb-2">No submitted titles found</h4>
                    <p class="text-gray-600 mb-4">Submit a final document to move a title here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Authors</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Similarity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($titles as $title)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $title->title }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $title->authors ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $title->submitted_at?->format('M d, Y h:i A') ?? '—' }}
                                    </td>

                                    {{-- Similarity column --}}
                                    <td class="px-6 py-4 text-sm">
                                        @if($title->finalDocument)
                                            @php
                                                $int = $title->finalDocument->plagiarism_internal;
                                                $ext = $title->finalDocument->plagiarism_external;

                                                $cls = function($v){
                                                    if ($v === null) return 'bg-gray-100 text-gray-600';
                                                    if ($v < 20)     return 'bg-green-100 text-green-800';
                                                    if ($v < 40)     return 'bg-yellow-100 text-yellow-800';
                                                    return            'bg-red-100 text-red-800';
                                                };
                                            @endphp

                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls($int) }}">
                                                    Int: {{ is_null($int) ? '—' : number_format($int, 2) . '%' }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls($ext) }}">
                                                    Ext: {{ is_null($ext) ? '—' : number_format($ext, 2) . '%' }}
                                                </span>
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="relative" x-data="{ open: false, dropdownTop: 0, dropdownLeft: 0 }">
    <button 
        @click="
            open = !open;
            if (open) {
                const rect = $el.getBoundingClientRect();
                dropdownTop = rect.bottom + window.scrollY;
                dropdownLeft = rect.right - 224; // 224px = w-56 width
            }
        "
        class="flex items-center justify-between w-32 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
    >
        Actions
        <svg class="w-4 h-4 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Dropdown -->
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed z-50 w-56 bg-white rounded-md shadow-lg ring-1 ring-gray-300 ring-opacity-5"
        :style="`top: ${dropdownTop}px; left: ${dropdownLeft}px;`"
    >
        <div class="py-1" role="none">
            @if($title->finalDocument)
                <!-- Copy Content -->
                <button 
                    onclick="copyContent({{ $title->finalDocument->id }})" 
                    class="dropdown-action flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 copy-btn"
                    data-id="{{ $title->finalDocument->id }}"
                    role="menuitem"
                >
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copy Content
                </button>

                <!-- View Document -->
                <a 
                    href="{{ route('documents.view', ['id' => $title->finalDocument->id]) }}"
                    class="dropdown-action flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    role="menuitem"
                >
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Document
                </a>

                <!-- Plagiarism Certificate -->
                <a 
                    href="{{ route('titles.plagiarism-certificate', $title->id) }}"
                    class="dropdown-action flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    target="_blank"
                    role="menuitem"
                >
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Plagiarism Certificate
                </a>

                <div class="border-t border-gray-100 my-1"></div>
            @endif

            <!-- Withdraw Submission -->
            <form method="POST" action="{{ route('titles.cancel', $title->id) }}" class="w-full">
                @csrf
                @method('PATCH')
                <button 
                    type="submit" 
                    class="dropdown-action flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-900"
                    role="menuitem"
                    onclick="return confirm('Are you sure you want to withdraw this submission?')"
                >
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Withdraw Submission
                </button>
            </form>
        </div>
    </div>
</div>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 🔽 Pagination -->
                <div class="px-6 py-4">
                    {{ $titles->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Comment Modal -->
    <div id="commentModal" class="fixed inset-0 hidden backdrop-blur-sm bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-lg rounded shadow p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-800">Admin Comment</h3>
            <p id="commentContent" class="text-gray-700 whitespace-pre-line"></p>
            <div class="text-right mt-4">
                <button onclick="closeCommentModal()"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </div>


    <style>
/* Force consistent font weight and color for dropdown actions */
.dropdown-action {
    font-weight: 400 !important; /* normal weight */
    color: #374151 !important; 
    outline: none !important;
}

/* Hover effect – only change background, not text */
.dropdown-action:hover {
    color: #374151 !important; /* keep same text color */
    font-weight: 400 !important;
    background-color: #e5e7eb !important;/* text-gray-700 */
}
</style>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <script>
        function openCommentModal(comment) {
            document.getElementById('commentContent').textContent = comment;
            document.getElementById('commentModal').classList.remove('hidden');
        }
        
        function closeCommentModal() {
            document.getElementById('commentModal').classList.add('hidden');
        }

        // Copy Content Function
        async function copyContent(documentId) {
            try {
                // Show loading state
                const button = document.querySelector(`.copy-btn[data-id="${documentId}"]`);
                const originalText = button.innerHTML;
                button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Copying...';
                button.disabled = true;

                // Fetch the document content
                const response = await fetch(`/documents/${documentId}/content`);
                
                if (!response.ok) {
                    throw new Error('Failed to fetch content');
                }

                const data = await response.json();
                
                if (!data.content) {
                    throw new Error('No content available');
                }

                // Create a temporary div to parse HTML and preserve formatting
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.content;

                // Use Clipboard API to copy with formatting
                const clipboardItem = new ClipboardItem({
                    'text/html': new Blob([data.content], { type: 'text/html' }),
                    'text/plain': new Blob([tempDiv.textContent || tempDiv.innerText || ''], { type: 'text/plain' })
                });

                await navigator.clipboard.write([clipboardItem]);
                
                // Show success toast
                showCopySuccess();
                
            } catch (error) {
                console.error('Copy failed:', error);
                alert('Failed to copy content: ' + error.message);
            } finally {
                // Restore button state
                const button = document.querySelector(`.copy-btn[data-id="${documentId}"]`);
                if (button) {
                    button.innerHTML = '<svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg> Copy Content';
                    button.disabled = false;
                }
            }
        }

        // Alternative simpler method (fallback)
        async function copyContentSimple(documentId) {
            try {
                const button = document.querySelector(`.copy-btn[data-id="${documentId}"]`);
                const originalText = button.innerHTML;
                button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Copying...';
                button.disabled = true;

                const response = await fetch(`/documents/${documentId}/content`);
                const data = await response.json();
                
                if (!data.content) {
                    throw new Error('No content available');
                }

                // Create temporary element to handle HTML content
                const tempElement = document.createElement('div');
                tempElement.innerHTML = data.content;
                document.body.appendChild(tempElement);

                // Select the content
                const range = document.createRange();
                range.selectNode(tempElement);
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);

                // Execute copy command
                const successful = document.execCommand('copy');
                selection.removeAllRanges();
                document.body.removeChild(tempElement);

                if (successful) {
                    showCopySuccess();
                } else {
                    throw new Error('Copy command failed');
                }
                
            } catch (error) {
                console.error('Copy failed:', error);
                alert('Failed to copy content: ' + error.message);
            } finally {
                const button = document.querySelector(`.copy-btn[data-id="${documentId}"]`);
                if (button) {
                    button.innerHTML = '<svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg> Copy Content';
                    button.disabled = false;
                }
            }
        }

        function showCopySuccess() {
            Swal.fire({
                icon: 'success',
                title: 'Content copied!',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end'
            });
        }

        // Use the modern Clipboard API if available, otherwise fallback
        if (!navigator.clipboard || !navigator.clipboard.write) {
            // Replace the copy function with simpler version if Clipboard API not available
            window.copyContent = copyContentSimple;
        }
    </script>
</x-userlayout>