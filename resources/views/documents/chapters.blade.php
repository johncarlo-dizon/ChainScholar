<x-userlayout>

<!-- HEADER -->
<div class="bg-blue-600 rounded-lg shadow p-6">
    <h2 class="text-3xl font-semibold mb-4 text-white">
        📚 Chapters for: <span class="text-gray-100">"{{ $title->title }}"</span>
    </h2>
    <p class="text-sm text-gray-300 mt-1">Manage and edit your research chapters below.</p>
</div>

<!-- FORM -->
<div class="container mx-auto px-4 py-10">
    <div class="bg-white p-6 rounded-lg shadow mx-auto mb-6">
        <form method="POST" action="{{ route('documents.store') }}">
            @csrf
            <input type="hidden" name="title_id" value="{{ $title->id }}">

            <label for="chapter" class="block text-sm font-bold text-blue-600 mb-1">Add New Chapter</label>
            <input type="text" name="chapter" id="chapter" placeholder="e.g., Introduction"
                class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-1 focus:ring-blue-400 focus:outline-none mb-2"
                required>
            <p class="text-sm text-gray-500 mb-4">Give your chapter a clear and concise title.</p>

            <div class="flex flex-col md:flex-row gap-3 md:items-center">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow-sm transition">
                    Create & Edit
                </button>

                <button type="button" onclick="openCombineModal()"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded shadow-sm">
                    Combine Docs
                </button>
            </div>
        </form>
    </div>

    <!-- CHAPTER LIST -->
    @if ($title->documents->isEmpty())
        <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded">
            <p>No chapters yet. Use the form above to create your first one.</p>
        </div>
    @else
        <div class="grid gap-3 mb-5">
            @foreach ($title->documents as $doc)
                <div class="bg-white px-4 py-1 rounded-lg shadow flex justify-between items-center hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <i data-feather="file-text" class="text-blue-500 w-5 h-5"></i>
                        <span class="text-sm font-semibold text-gray-800">{{ $doc->chapter }}</span>
                    </div>
                    <div class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('documents.edit', $doc->id) }}"
                            class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-indigo-600 rounded hover:bg-green-50">
                            <i data-feather="edit-3" class="w-4 h-4 mr-2"></i> Edit
                        </a>
                        <button onclick="openDeleteModal({{ $doc->id }}, '{{ $doc->chapter }}')"
                            class="inline-flex items-center px-3 py-1.5 border border-red-600 text-red-600 rounded hover:bg-red-50">
                            <i data-feather="trash-2" class="w-4 h-4 mr-2"></i> Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- COMBINE MODAL (Enhanced with name input and checkboxes) -->
<div id="combineModal" class="fixed inset-0 hidden items-center justify-center z-50">
    <div id="combineModalOverlay" class="absolute inset-0 backdrop-blur-sm bg-transparent"></div>
    <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-2xl w-full mx-4 z-10">
        <h2 class="text-xl font-bold mb-4 text-blue-600">📑 Combine Chapters</h2>

        <form method="POST" action="{{ route('documents.combine.custom', $title->id) }}">
            @csrf
            <input type="hidden" name="title_id" value="{{ $title->id }}">

            <div class="mb-4">
                <label for="combined_name" class="block font-semibold text-sm text-gray-700 mb-1">Combined Document Name</label>
                <input type="text" name="combined_name" id="combined_name" required
                       placeholder="e.g., Final Research Draft"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:ring focus:ring-blue-200">
            </div>

            <p class="text-sm text-gray-600 mb-2">✔️ Select chapters to include and drag to reorder:</p>

            <div id="chapter-order-list" class="space-y-2">
                @foreach ($title->documents as $doc)
                    @if ($doc->format === 'separate')
                        <div class="flex items-center justify-between bg-gray-100 p-2 rounded cursor-move" data-id="{{ $doc->id }}">
                            <div class="flex items-center space-x-3">
                                <span class="cursor-move text-gray-500 font-bold text-lg">☰</span>
                                <input type="checkbox" name="included_ids[]" value="{{ $doc->id }}" checked class="form-checkbox h-4 w-4 text-blue-600">
                                <span class="text-sm">{{ $doc->chapter }}</span>
                            </div>
                            <input type="hidden" name="ordered_ids[]" value="{{ $doc->id }}">
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-5 flex justify-end space-x-3">
                <button type="button" id="combineCancelBtn"
                        class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    Combine Now
                </button>
            </div>
        </form>
    </div>
</div>


<!-- DELETE MODAL -->
<div id="deleteModal" class="fixed inset-0 hidden items-center justify-center z-50">
    <div id="deleteModalOverlay" class="absolute inset-0 backdrop-blur-sm bg-transparent"></div>
    <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-xl w-full mx-4 z-10">
        <h2 class="text-xl font-bold mb-4 text-red-600">🗑️ Confirm Chapter Deletion</h2>

        <p class="text-gray-700 mb-4">Are you sure you want to delete the chapter: 
            <span class="font-semibold" id="deleteChapterName"></span>?
        </p>

        <form method="POST" id="deleteForm">
            @csrf
            @method('DELETE')
            <div class="mt-5 flex justify-end space-x-3">
                <button type="button" id="deleteCancelBtn"
                    class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded">
                    Delete Chapter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://unpkg.com/feather-icons"></script>

<script>
    // Combine Modal
    const combineModal = document.getElementById('combineModal');
    const combineOverlay = document.getElementById('combineModalOverlay');
    const combineCancelBtn = document.getElementById('combineCancelBtn');

    function openCombineModal() {
        combineModal.classList.remove('hidden');
        combineModal.classList.add('flex');
        feather.replace();
    }

    function closeCombineModal() {
        combineModal.classList.add('hidden');
        combineModal.classList.remove('flex');
    }

    combineCancelBtn.addEventListener('click', closeCombineModal);
    combineOverlay.addEventListener('click', closeCombineModal);

    // Delete Modal
    const deleteModal = document.getElementById('deleteModal');
    const deleteOverlay = document.getElementById('deleteModalOverlay');
    const deleteCancelBtn = document.getElementById('deleteCancelBtn');
    const deleteForm = document.getElementById('deleteForm');
    const deleteChapterName = document.getElementById('deleteChapterName');

    function openDeleteModal(id, chapterName) {
        deleteChapterName.textContent = chapterName;
        deleteForm.action = `/documents/${id}`;
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
        feather.replace();
    }

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
    }

    deleteCancelBtn.addEventListener('click', closeDeleteModal);
    deleteOverlay.addEventListener('click', closeDeleteModal);

    // Sortable Init
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('chapter-order-list');
        if (list) {
            new Sortable(list, {
                animation: 150,
                handle: '.cursor-move',
            });
        }
        feather.replace();
    });
</script>




</x-userlayout>
