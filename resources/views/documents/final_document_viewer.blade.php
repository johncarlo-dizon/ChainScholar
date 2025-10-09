<x-userlayout> 



<div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">     View Submitted Research</h2>
         <p class="text-sm-5 text-gray-300 mt-1">  {{ $document->titleRelation->title ?? 'Untitled Title' }}</p>
    </div>
   
  </div>
</div>


     





    <div class="container mx-auto px-4 pt-2 pb-8">
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
    @php
  // Prefer new fields; fall back to legacy score for internal
  $int = $document->plagiarism_internal ?? $document->plagiarism_score;
  $ext = $document->plagiarism_external;

  // severity colors: <20 green, 20–39 yellow, 40+ red; null = gray
  $cls = function($v){
    if ($v === null) return 'bg-gray-100 text-gray-700';
    if ($v < 20)     return 'bg-green-100 text-green-800';
    if ($v < 40)     return 'bg-yellow-100 text-yellow-800';
    return            'bg-red-100 text-red-800';
  };
@endphp

<div class="space-y-1">
  <div class="font-semibold text-gray-700">Similarity</div>
  <div class="flex items-center gap-2">
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls($int) }}">
      Internal: {{ is_null($int) ? '—' : number_format($int, 2) . '%' }}
    </span>
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls($ext) }}">
      External: {{ is_null($ext) ? '—' : number_format($ext, 2) . '%' }}
    </span>
  </div>
</div>

</div>

                    </div>



                       <div class="space-y-4 hidden">
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
<a href="{{ auth()->user()->role === 'ADMIN'
              ? route('admin.titles.submitted')
              : (auth()->user()->role === 'ADVISER'
                  ? route('adviser.advised.index')
                  : route('documents.submitted')) }}"
   class="text-sm text-blue-500 hover:text-blue-700 transition">
    ← Back to Submitted Titles
</a>

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
