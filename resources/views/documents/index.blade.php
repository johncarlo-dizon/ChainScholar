<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">My Documents</h2>
    </div>

    <div class="container mx-auto px-4 mt-2">
        <div class="flex justify-end items-center mb-6">
    
       
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($documents->isEmpty())
                <div class="text-center py-12">
                    <h4 class="text-xl font-semibold mb-2">No documents found</h4>
                    <p class="text-gray-600 mb-4">Create your first document to get started</p>
                    <a href="{{ route('documents.create') }}" 
                       class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Create Document
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($documents as $document)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $document->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $document->updated_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 space-x-2">
                                        <a href="{{ route('documents.edit', $document->id) }}" 
                                           class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded hover:bg-blue-50">
                                            <!-- Pencil icon -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6-3 3-3-3 3-3z" />
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="{{ route('documents.show', $document->id) }}" 
                                           class="inline-flex items-center px-3 py-1.5 border border-green-600 text-green-600 rounded hover:bg-green-50">
                                            <!-- Eye icon -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                              <div x-data="{ open: false }" class="inline-block">
  <!-- Delete button -->
<button
    type="button"
    class="inline-flex items-center px-3 py-1.5 border border-red-600 text-red-600 rounded hover:bg-red-50 transition"
    onclick="showModal({{ $document->id }})"
>
    <!-- Trash icon -->
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 
            0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
    </svg>
    Delete
</button>

<!-- Modal -->
<div id="delete-modal" class="fixed inset-0 hidden items-center justify-center z-50">
    <!-- The blur overlay -->
    <div id="modal-overlay" class="absolute inset-0 backdrop-blur-sm bg-transparent"></div>

    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-lg w-full mx-4 z-10">
        <p class="text-lg font-semibold mb-5 text-gray-900">Are you sure you want to delete this document?</p>
        <div class="flex justify-end space-x-3">
            <button id="cancel-btn"
                class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                Cancel
            </button>
            <form id="delete-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('delete-modal');
    const overlay = document.getElementById('modal-overlay');
    const cancelBtn = document.getElementById('cancel-btn');
    const deleteForm = document.getElementById('delete-form');

    // Show modal and set form action dynamically
    function showModal(documentId) {
        deleteForm.action = `/documents/${documentId}`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // Hide modal helper
    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Cancel button closes modal
    cancelBtn.addEventListener('click', hideModal);

    // Clicking outside modal closes modal
    overlay.addEventListener('click', hideModal);
</script>





</div>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-userlayout>
