<x-userlayout>
   
<div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">Research Library</h2>
    </div>
   
  </div>
</div>



    <div class="container mx-auto px-4 py-4">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left: Document Display -->
            <div class="main-container w-full lg:w-2/3">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="mb-6">
                            <label class="block mb-2 font-bold text-blue-600">Title</label>
                            <input 
                                type="text" 
                                value="{{ $title->title }}" 
                                disabled 
                                class="w-full bg-gray-100 border border-gray-300 rounded-md px-4 py-3 text-lg"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block mb-2 font-bold text-blue-600">Document Content</label>
                            <div class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-word-count editor-container_include-fullscreen" id="viewer-container">
                                <div class="editor-container__editor">
                                    <div class="ck-content w-full min-h-[600px] bg-white border border-gray-300 rounded-md shadow-sm leading-relaxed text-base">
                                        {!! $title->finalDocument->content ?? '<em>No content available</em>' !!}
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
                            <p><span class="font-semibold">Authors:</span> {{ $title->authors}}</p>
                             <p><span class="font-semibold">Submitted by:</span> {{ $title->user->name }}</p>
                 <p>
  <span class="font-semibold">Adviser:</span>
  {{ optional($title->primaryAdviser)->name ?? '—' }}
</p>

                            <p><span class="font-semibold">Submitted on:</span> {{ $title->submitted_at->format('F d, Y h:i A') }}</p>
                            <p><span class="font-semibold">Research Type:</span> {{ $title->research_type }}</p>
                            <p><span class="font-semibold hidden">Category:</span> {{ $title->category }}</p>
                            <p class="hidden"><span class="font-semibold hidden">Sub-Category:</span> {{ $title->sub_category ?? '—' }}</p>
  
                        </div>
                    </div>


 

              

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-700">Actions</h3>
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('dashboard') }}"
                               class="text-sm text-blue-500 hover:text-blue-700 transition">
                                Back to Research Search
                            </a>
                        </div>
                    </div>

                    
                </div>


                <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                  <h3 class="text-lg font-semibold text-gray-700">Abstract</h3>
                  <div class="text-sm text-gray-600">
                      <p class="whitespace-pre-line">{{ $title->abstract ?? '—' }}</p>
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
