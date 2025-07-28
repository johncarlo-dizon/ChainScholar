<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">📤 Submitted Titles</h2>
        <p class="text-sm text-blue-100">These are titles you've submitted for review or have been approved.</p>
    </div>

    <div class="container mx-auto px-4 mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- 🔍 Search + Filter -->
        <form method="GET" class="flex items-center gap-4 mb-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full max-w-sm border border-gray-300 rounded px-4 py-2 text-sm"
                   placeholder="Search by title...">
            <button type="submit"
                    class="px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                Search
            </button>
            <select name="status" onchange="this.form.submit()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
            </select>
      
        </form>

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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($titles as $title)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $title->title }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($title->status === 'approved') bg-green-100 text-green-800
                                            @elseif($title->status === 'returned') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($title->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $title->submitted_at?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-2">
                                        @if($title->finalDocument)
                                            <a href="{{ route('documents.view', $title->finaldocument_id) }}"
                                               class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded hover:bg-blue-50">
                                                View Document
                                            </a>
                                        @endif

                                        @if($title->status === 'returned' && $title->review_comments)
                                            <button onclick="openCommentModal(`{{ addslashes($title->review_comments) }}`)"
                                                    class="inline-flex items-center px-3 py-1.5 border border-yellow-600 text-yellow-600 rounded hover:bg-yellow-50">
                                                Open Comment
                                            </button>
                                        @endif

                                        <form method="POST" action="{{ route('titles.cancel', $title->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 border border-red-600 text-red-600 rounded hover:bg-red-50">
                                                Cancel {{ $title->status === 'approved' ? 'Approval' : 'Submission' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Comment Modal -->
    <div id="commentModal" class="fixed inset-0 hidden  backdrop-blur-sm bg-opacity-50  flex items-center justify-center z-50">
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

    <script>
        function openCommentModal(comment) {
            document.getElementById('commentContent').textContent = comment;
            document.getElementById('commentModal').classList.remove('hidden');
        }

        function closeCommentModal() {
            document.getElementById('commentModal').classList.add('hidden');
        }
    </script>
</x-userlayout>
