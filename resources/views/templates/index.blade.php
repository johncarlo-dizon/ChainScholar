 <!-- templates/index.blade.php -->
<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">ChainScholar Templates</h2>
    </div>

    <div class="container mx-auto px-4 mt-5">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(request('use_for') === 'chapter' && request('document_id'))
            <a href="{{ route('documents.edit', ['document' => request('document_id')]) }}"
            class="inline-block px-4 py-2 mb-6 bg-white  shadow rounded hover:bg-gray-50 transition">
                Cancel and Return to Editing
            </a>
        @endif


        <div class="bg-white shadow rounded-lg p-6">
            @if($templates->isEmpty())
                <div class="text-center py-12">
                    <h4 class="text-xl font-semibold mb-2">No templates found</h4>
                    <p class="text-gray-600 mb-4">Create your first template to get started</p>
                    <a href="{{ route('templates.create') }}" 
                       class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Create Template
                    </a>
                </div>
            @else
                <!-- Grid Layout -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    <div class="flex flex-col items-center bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-lg transition duration-300">
                        <div class="w-24 h-32 bg-white border border-gray-300 mb-4 p-2 flex items-center justify-center">
                            <img src="{{ asset('storage/images/blank.png') }}" alt="Admin Template" class="max-w-full max-h-full object-contain">
                        </div>
                        <h3 class="text-sm font-medium text-center text-gray-700 mb-2 truncate w-full">Blank</h3>
                        <a href="{{ route('templates.create') }}" 
                           class="inline-flex items-center px-3 py-1.5 mt-2 bg-blue-500 text-white rounded hover:bg-blue-700 text-sm">
                            Create
                        </a>
                    </div>

                    @foreach($templates->sortBy(function($template) {
                        return $template->user->position === 'admin' ? 1 : 0;
                    }) as $template)
                        <div class="flex flex-col items-center bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-lg transition duration-300">
                         
                                <div class="w-24 h-32 bg-white border border-blue-300 mb-4 p-2 flex items-center justify-center">
                                    <img src="{{ asset('storage/previews/' . $template->id . '.png') }}" 
                                    alt="Preview" 
                                    class="max-w-full max-h-full object-contain"
                                    onerror="this.src='{{ asset('storage/images/tempuser.png') }}'">
                                </div>
                            

                            <h3 class="text-sm font-medium text-center text-gray-700 mb-2 truncate w-full">{{ $template->name }}</h3>



                      @auth 
                        @if(auth()->user()->position === 'user')
                            <div class="flex flex-wrap justify-center gap-2 mt-2">
                                <a href="{{ route('templates.use', ['template' => $template->id, 'use_for' => request('use_for'), 'document_id' => request('document_id')]) }}"
                                    class="w-25 text-center px-3 py-1.5 bg-blue-500 text-white rounded hover:bg-blue-700 text-sm">
                                        Use
                                    </a>


                                @if($template->user->position === 'user' || auth()->user()->position === 'admin')
                                    <a href="{{ route('templates.edit', $template->id) }}" 
                                        class="inline-flex items-center px-1 py-1.5 text-yellow-500   rounded hover:text-yellow-600 text-sm">
                                         <i data-feather="edit" class="w-4 h-4 "></i>
                                    </a>

                                    <!-- Delete Button (opens modal) -->
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-1 py-1.5   text-red-500 rounded hover:text-red-700 text-sm"
                                        onclick="showDeleteModal({{ $template->id }})"
                                    >
                                         <i data-feather="trash" class="w-4 h-4 "></i>
                                    </button>
                                @endif
                            </div>
                        @endif

                        @if(auth()->user()->position === 'admin')
                         <div class="flex flex-wrap justify-center gap-2 mt-2">
                             <a href="{{ route('templates.edit', $template->id) }}" 
                                        class="inline-flex items-center px-1 py-1.5 text-yellow-500   rounded hover:text-yellow-600 text-sm">
                                         <i data-feather="edit" class="w-4 h-4 "></i>
                                    </a>

                              <div x-data="{ open: false }" class="inline-block">
                                      <button
                                        type="button"
                                        class="inline-flex items-center px-1 py-1.5   text-red-500 rounded hover:text-red-700 text-sm"
                                        onclick="showDeleteModal({{ $template->id }})"
                                    >
                                         <i data-feather="trash" class="w-4 h-4 "></i>
                                    </button>
                                </div>
                                  </div>
                        @endif
                    @endauth















                            
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>


    <!-- Delete Modal -->
<div id="template-delete-modal" class="fixed inset-0 hidden items-center justify-center z-50">
    <div id="template-modal-overlay" class="absolute inset-0 backdrop-blur-xl bg-transparent"></div>

    <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-lg w-full mx-4 z-10">
        <p class="text-lg font-semibold mb-5 text-gray-900">Are you sure you want to delete this template?</p>
        <div class="flex justify-end space-x-3">
            <button id="template-cancel-btn"
                class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                Cancel
            </button>
            <form id="template-delete-form" method="POST">
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
    const templateModal = document.getElementById('template-delete-modal');
    const templateOverlay = document.getElementById('template-modal-overlay');
    const templateCancelBtn = document.getElementById('template-cancel-btn');
    const templateDeleteForm = document.getElementById('template-delete-form');

    function showDeleteModal(templateId) {
        templateDeleteForm.action = `/templates/${templateId}`; // Resourceful route
        templateModal.classList.remove('hidden');
        templateModal.classList.add('flex');
    }

    function hideDeleteModal() {
        templateModal.classList.add('hidden');
        templateModal.classList.remove('flex');
    }

    templateCancelBtn.addEventListener('click', hideDeleteModal);
    templateOverlay.addEventListener('click', hideDeleteModal);
</script>

</x-userlayout>
