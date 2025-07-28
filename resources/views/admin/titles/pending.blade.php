<x-userlayout>
    <div class="bg-yellow-500 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-2 text-white">⏳ Pending Titles</h2>
        <p class="text-sm text-yellow-100">Review and approve or return submitted documents.</p>
    </div>

    <div class="container mx-auto px-4 mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($titles->isEmpty())
                <div class="text-center py-12">
                    <h4 class="text-xl font-semibold mb-2">No pending titles</h4>
                    <p class="text-gray-600">All pending submissions will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($titles as $title)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $title->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $title->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $title->submitted_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 text-sm space-x-2">
                                        <a href="{{ route('admin.documents.review', $title->finaldocument_id) }}"
                                           class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded hover:bg-blue-50">
                                            View
                                        </a>
                                        <form action="{{ route('admin.titles.approve', $title->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 border border-green-600 text-green-600 rounded hover:bg-green-50">
                                                Approve
                                            </button>
                                        </form>
                                        <button onclick="openReturnModal({{ $title->id }})"
                                                class="inline-flex items-center px-3 py-1.5 border border-red-600 text-red-600 rounded hover:bg-red-50">
                                            Return
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Return Modal -->
    <div id="return-modal" class="fixed inset-0 hidden bg-transparent  backdrop-blur-sm bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-lg rounded shadow p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-800">Return Document with Comments</h3>
            <form method="POST" action="{{ route('admin.titles.return') }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="title_id" id="return_title_id">
                <textarea name="review_comments" rows="5" required
                          class="w-full border border-gray-300 rounded px-4 py-2"
                          placeholder="Enter your comments here..."></textarea>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeReturnModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Return</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReturnModal(id) {
            document.getElementById('return_title_id').value = id;
            document.getElementById('return-modal').classList.remove('hidden');
        }
        function closeReturnModal() {
            document.getElementById('return-modal').classList.add('hidden');
        }
    </script>
</x-userlayout>
