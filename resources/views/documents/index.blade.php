<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">My Titles</h2>
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
                    <h4 class="text-xl font-semibold mb-2">No titles found</h4>
                    <p class="text-gray-600 mb-4">Create your first title to get started</p>
                    <a href="{{ route('titles.verify') }}"
                       class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Verify & Create Title
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Updated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($titles as $title)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $title->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $title->updated_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 space-x-2">
                                        <a href="{{ route('titles.chapters', $title->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 border border-green-600 text-green-600 rounded hover:bg-green-50">
                                            Open
                                        </a>

                                        <button type="button"
                                            onclick="showModal({{ $title->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-red-600 text-red-600 rounded hover:bg-red-50 transition">
                                            Delete
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

    <!-- Delete Modal -->
    <div id="delete-modal" class="fixed inset-0 hidden items-center justify-center z-50">
        <div id="modal-overlay" class="absolute inset-0 backdrop-blur-sm bg-transparent"></div>
        <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-lg w-full mx-4 z-10">
            <p class="text-lg font-semibold mb-5 text-gray-900">Are you sure you want to delete this title and all its chapters?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancel-btn" class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">Cancel</button>
                <form id="delete-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('delete-modal');
        const overlay = document.getElementById('modal-overlay');
        const cancelBtn = document.getElementById('cancel-btn');
        const deleteForm = document.getElementById('delete-form');

        function showModal(titleId) {
            deleteForm.action = `/titles/${titleId}`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        cancelBtn.addEventListener('click', hideModal);
        overlay.addEventListener('click', hideModal);
    </script>
</x-userlayout>
