<x-userlayout>

 <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">📚 Chapters for: <span class="text-gray-100">"{{ $title->title }}"</h2>
          <p class="text-sm text-gray-300 mt-1">Manage and edit your research chapters below.</p>
    </div>

    <div class="container mx-auto px-4 py-10">


 <div class="bg-white p-6 rounded-lg shadow  mx-auto mb-6">
          
            <form method="POST" action="{{ route('documents.store') }}">
                @csrf
                <input type="hidden" name="title_id" value="{{ $title->id }}">

                <div class="mb-4">
                    <label for="chapter" class="block text-sm font-bold text-blue-600 mb-1">Add New Chapter</label>
                    <input type="text" name="chapter" id="chapter" placeholder="e.g., Introduction"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-1 focus:ring-blue-400 focus:outline-none" required>
                    <p class="text-sm text-gray-500 mt-1">Give your chapter a clear and concise title.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow-sm transition">
                        Create & Open Editor
                    </button>
                </div>
            </form>
        </div>

        
        @if ($title->documents->isEmpty())
            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded ">
                <p>No chapters yet. Use the form above to create your first one.</p>
            </div>
        @else
            <div class="grid gap-4 mb-10">
                @foreach ($title->documents as $doc)
                    <div class="bg-white p-5 rounded-lg shadow  flex justify-between items-center hover:shadow-md transition">
                        <div class="flex items-center space-x-3">
                           <i data-feather="file-text" class="text-blue-500 w-5 h-5"></i>

                            <span class="text-sm font-semibold text-gray-800">{{ $doc->chapter }}</span>
                        </div>
                        <a href="{{ route('documents.edit', $doc->id) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                <i data-feather="edit-3" class="w-4 h-4 mr-2"></i>  Edit
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

       
    </div>
</x-userlayout>
