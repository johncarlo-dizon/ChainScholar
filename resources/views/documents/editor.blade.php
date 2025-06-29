 <!-- documents/editor.blade.php -->
<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl text-white font-semibold mb-4">
            {{ isset($document) ? 'Edit' : 'New' }} Document — {{ $document->chapter ?? 'Untitled Chapter' }}
        </h2>
    </div>

    <div class="container mx-auto px-4 py-8">
        <form 
            action="{{ route('documents.update', $document) }}" 
            method="POST" 
            class="document-form" 
            id="doc-form">
            @csrf
            @method('PUT')

            <input type="hidden" name="title_id" value="{{ $document->title_id }}">
            <input type="hidden" name="chapter" value="{{ $document->chapter }}">

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left: Content Editor -->
                <div class="main-container w-full lg:w-2/3">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <div class="mb-6">
                                <label class="block mb-2 font-bold text-blue-600">Chapter</label>
                                <input 
                                    type="text" 
                                    value="{{ $document->chapter }}" 
                                    disabled 
                                    class="w-full bg-gray-100 border border-gray-300 rounded-md px-4 py-3 text-lg"
                                >
                            </div>

                            <div class="mb-6">
                                <label for="editor" class="block mb-2 font-bold text-blue-600">Document Content</label>
                                <div class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-word-count editor-container_include-fullscreen" id="editor-container">
                                    <div class="editor-container__editor">
                           @php
    $prefillContent = session('templateContent') ?? old('content', $document->content ?? '');
@endphp

<textarea 
    name="content" 
    id="editor" 
    class="min-h-[600px] w-full p-4 bg-white border border-gray-300 rounded-md hidden"
>{{ $prefillContent }}</textarea>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Sidebar -->
                <div class="w-full lg:w-1/3 lg:sticky lg:top-6 h-fit">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full space-y-8">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-700">Document Info</h3>
                            </div>
                            <div id="editor-word-count" class="text-sm text-gray-500">
                                <!-- Word count will be injected here -->
                            </div>
                        </div>


                        

                        <a href="{{ route('templates.index', ['use_for' => 'chapter', 'document_id' => $document->id]) }}"
   class="inline-block px-4 py-2 mb-4 bg-green-600 text-white rounded hover:bg-green-700 transition">
    Use Template
</a>


                        <div class="pt-2 border-t border-gray-200">
                            <div class="flex justify-between">
                                <a href="{{ route('titles.chapters', $document->title_id) }}"
                                   class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition duration-200">
                                    Back to Chapters
                                </a>
                                <button type="submit"
                                        class="flex-1 text-center ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-sm focus:ring-2 focus:ring-blue-300">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- CKEditor Scripts -->
    <link rel="stylesheet" href="{{ asset('assets/editor.css') }}">
    <script src="{{ asset('assets/editor.js') }}"></script>

    <style>
        .ck-content {
            min-height: 600px;
            background-color: white;
            color: #000;
        }
        .ck-content ul, .ck-content ol {
            padding-left: 2rem;
            list-style: disc;
        }
        .ck-content ol {
            list-style: decimal;
        }
        .ck-content li {
            margin-bottom: 0.3em;
        }
        .ck-powered-by {
            display: none !important;
        }
        .ck.ck-toolbar {
            position: sticky !important;
            top: 6rem;
            z-index: 100;
            background-color: white;
        }
    </style>
</x-userlayout>
