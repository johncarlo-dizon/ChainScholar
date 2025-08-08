<x-userlayout> 
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl text-white font-semibold mb-4">
            Review Final Document — {{ $document->titleRelation->title ?? 'Untitled Title' }}
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
                                    <div class="ck-content w-full min-h-[600px] bg-white border border-gray-300 rounded-md shadow-sm leading-relaxed text-base">
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
                <form action="{{ route('admin.titles.return') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full space-y-8">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="title_id" value="{{ $document->titleRelation->id }}">

                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-700">Document Info</h3>
                    <div class="text-sm text-gray-500 space-y-1">
                        <p><strong>Submitted by:</strong> {{ $document->user->name }}</p>
                        <p><strong>Submitted on:</strong> {{ $document->created_at->format('F d, Y h:i A') }}</p>
                        <p><strong>Authors:</strong> {{ $document->titleRelation->authors ?? '—' }}</p>
                        <p><strong>Research Type:</strong> {{ $document->titleRelation->research_type }}</p>                 
                        <p><strong>Plagiarism Score:</strong> {{ $document->plagiarism_score ?? '—' }}%</p>
                    </div>

                    </div>

                    <!-- Comments -->
                    <div class="space-y-2">
                        <label for="review_comments" class="block text-sm font-medium text-gray-700">Admin Comment</label>
                        <textarea name="review_comments" rows="4"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm shadow-sm"
                                  placeholder="Write a reason or feedback here..."></textarea>
                        <button type="submit"
                            class="block w-full text-center px-4 py-2 border border-gray-300 text-white rounded-lg hover:bg-red-700 bg-red-600  transition">
                            Return
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="pt-2 border-t border-gray-200 space-y-2">
                        <div class="flex justify-between gap-2">
                            <a href="{{ route('admin.titles.pending') }}"
                            class="w-1/2 text-center px-4 py-2 text-gray-600 hover:bg-red-100 rounded-lg shadow-sm">
                                ← Back to Pending
                            </a>
                                </form>
                            <form action="{{ route('admin.titles.approve', $document->titleRelation->id) }}" method="POST" class="w-1/2">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm">
                                    Approve
                                </button>
                            </form>
                        </div>
                    </div>
            
            </div>
        </div>
    </div>

    <!-- Styles for viewer only -->
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
