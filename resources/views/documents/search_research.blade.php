<x-userlayout>
   
<x-header.bar
  title="Research Library"
  subtitle="Your collection of academic resources"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>



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
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full space-y-5">
                    <div class="space-y-2">
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


 
{{-- ABSTRACT (Fixed Height - Tailwind Only) --}}
@php
  $abs = trim((string)($title->abstract ?? ''));
  $hasAbstract = $abs !== '';
@endphp
<div role="region" aria-labelledby="abstract-heading">
  <h3 id="abstract-heading" class="font-semibold text-gray-900 text-base mb-3 flex items-center gap-2">
    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Abstract
    @if($hasAbstract)
      <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
        {{ str_word_count($abs) }} words
      </span>
    @endif
  </h3>
  
  <div class="border border-gray-300 rounded-lg bg-white shadow-sm overflow-hidden h-48">
    @if($hasAbstract)
      <div class="h-40 overflow-y-auto p-4 text-gray-700 leading-relaxed text-sm
                  scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100
                  hover:scrollbar-thumb-gray-400" style="height: 300px;"
           tabindex="0"
           aria-label="Abstract content - scrollable area"
           role="textbox"
           aria-readonly="true">
        <div class="whitespace-pre-wrap break-words">
          {{ $abs }}
        </div>
      </div>
    @else
      <div class="h-48 flex items-center justify-center text-gray-500 italic p-4">
        <div class="text-center">
          <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-sm">No abstract provided</p>
        </div>
      </div>
    @endif
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
