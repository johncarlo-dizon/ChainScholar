<x-userlayout> 
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl text-white font-semibold mb-4">
            View Final Document — {{ $document->titleRelation->title ?? 'Untitled Title' }}
        </h2>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left: Document Display -->
            <div class="main-container w-full lg:w-2/3">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="mb-6">
                            <label class="block mb-2 font-bold text-blue-600">Title</label>
                            <input 
                                type="text" 
                                value="{{ $document->titleRelation->title }}" 
                                disabled 
                                class="w-full bg-gray-100 border border-gray-300 rounded-md px-4 py-3 text-lg"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block mb-2 font-bold text-blue-600">Document Content</label>
                            <div class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-word-count editor-container_include-fullscreen" id="viewer-container">
                                <div class="editor-container__editor">
                                    <div class="ck-content w-full min-h-[600px]  bg-white border border-gray-300 rounded-md shadow-sm leading-relaxed text-base">
                                        {!! $document->content !!}
                                    </div>
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
                    <div class="text-sm text-gray-500 space-y-1">
    <p><span class="font-semibold">Submitted by:</span> {{ $document->user->name }}</p>
    <p><span class="font-semibold">Submitted on:</span> {{ $document->created_at->format('F d, Y h:i A') }}</p>
    <p><span class="font-semibold">Authors:</span> {{ $document->titleRelation->authors ?? '—' }}</p>
    <p><span class="font-semibold">Research Type:</span> {{ $document->titleRelation->research_type }}</p>
    <p><span class="font-semibold">Status:</span> 
        <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold 
            @if($document->titleRelation->status == 'pending') bg-yellow-100 text-yellow-800 
            @elseif($document->titleRelation->status == 'approved') bg-green-100 text-green-800 
            @elseif($document->titleRelation->status == 'returned') bg-red-100 text-red-800 
            @else bg-gray-100 text-gray-600 @endif">
            {{ ucfirst($document->titleRelation->status) }}
        </span>
    </p>
    <p><span class="font-semibold">Plagiarism Score:</span> {{ $document->plagiarism_score ?? '—' }}%</p>
</div>

                    </div>



                       <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-700">Comment</h3>
                        </div>
                        <div class="text-sm text-gray-500 space-y-1">
                          
                            <p> {{ $document->titleRelation->review_comments ?? '—' }}</p>
                      
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-700">Actions</h3>
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('documents.submitted') }}"
                               class="text-sm text-blue-500 hover:text-blue-700 transition">
                                ← Back to Submitted Titles
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles to mirror editor.blade.php -->
    <link rel="stylesheet" href="{{ asset('assets/editor.css') }}">
    <style>
        .ck-content {
            background-color: white;
            color: #000;
            min-height: 600px;
            font-size: 1rem;
            line-height: 1.75;
            word-wrap: break-word;
            padding: 1.5rem;
        }

        .ck-content ul,
        .ck-content ol {
            padding-left: 2rem;
        }

        .ck-content ul {
            list-style-type: disc;
        }

        .ck-content ol {
            list-style-type: decimal;
        }

        .ck-content li {
            margin-bottom: 0.3em;
        }

        .editor-container__editor {
            background-color: white;
        }

        .ck.ck-toolbar,
        .ck-powered-by {
            display: none !important;
        }
    </style>
</x-userlayout>
