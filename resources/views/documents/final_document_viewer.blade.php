<x-userlayout> 
<x-header.bar
  title="Submitted Research"
  subtitle="Viewing: {{ $document->titleRelation->title ?? 'Untitled Title' }}"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

    <div class="container mx-auto px-4 pt-2 pb-8">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left: Document Display -->
            <div class="main-container w-full lg:w-2/3">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                     

                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block font-bold text-blue-600">Document Content</label>
                                <!-- Copy Button -->
                                <button onclick="copyContent({{ $document->id }})" 
                                        class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition copy-btn"
                                        data-id="{{ $document->id }}"
                                        title="Copy document content to clipboard">
                                    Copy Content
                                </button>
                            </div>
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

{{-- ABSTRACT (Fixed Height - Tailwind Only) --}}
@php
  $abs = trim((string)($document->titleRelation->abstract ?? ''));
  $hasAbstract = $abs !== '';
@endphp
<div class="mt-6" role="region" aria-labelledby="abstract-heading">
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

    <!-- Copy functionality script -->
    <script>
        // Copy Content Function
        async function copyContent(documentId) {
            try {
                // Show loading state
                const button = document.querySelector(`.copy-btn[data-id="${documentId}"]`);
                const originalText = button.textContent;
                button.textContent = 'Copying...';
                button.disabled = true;

                // Fetch the document content
                const response = await fetch(`/documents/${documentId}/content`);
                
                if (!response.ok) {
                    throw new Error('Failed to fetch content');
                }

                const data = await response.json();
                
                if (!data.content) {
                    throw new Error('No content available');
                }

                // Create a temporary div to parse HTML and preserve formatting
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.content;

                // Use Clipboard API to copy with formatting
                const clipboardItem = new ClipboardItem({
                    'text/html': new Blob([data.content], { type: 'text/html' }),
                    'text/plain': new Blob([tempDiv.textContent || tempDiv.innerText || ''], { type: 'text/plain' })
                });

                await navigator.clipboard.write([clipboardItem]);
                
                // Show success toast
                showCopySuccess();
                
            } catch (error) {
                console.error('Copy failed:', error);
                alert('Failed to copy content: ' + error.message);
            } finally {
                // Restore button state
                const button = document.querySelector(`.copy-btn[data-id="${documentId}"]`);
                if (button) {
                    button.textContent = originalText;
                    button.disabled = false;
                }
            }
        }

        // Alternative simpler method (fallback)
        async function copyContentSimple(documentId) {
            try {
                const button = document.querySelector(`.copy-btn[data-id="${documentId}"]`);
                const originalText = button.textContent;
                button.textContent = 'Copying...';
                button.disabled = true;

                const response = await fetch(`/documents/${documentId}/content`);
                const data = await response.json();
                
                if (!data.content) {
                    throw new Error('No content available');
                }

                // Create temporary element to handle HTML content
                const tempElement = document.createElement('div');
                tempElement.innerHTML = data.content;
                document.body.appendChild(tempElement);

                // Select the content
                const range = document.createRange();
                range.selectNode(tempElement);
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);

                // Execute copy command
                const successful = document.execCommand('copy');
                selection.removeAllRanges();
                document.body.removeChild(tempElement);

                if (successful) {
                    showCopySuccess();
                } else {
                    throw new Error('Copy command failed');
                }
                
            } catch (error) {
                console.error('Copy failed:', error);
                alert('Failed to copy content: ' + error.message);
            } finally {
                const button = document.querySelector(`.copy-btn[data-id="${documentId}"]`);
                if (button) {
                    button.textContent = 'Copy Content';
                    button.disabled = false;
                }
            }
        }

        function showCopySuccess() {
            Swal.fire({
                icon: 'success',
                title: 'Content copied!',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end'
            });
        }

        // Use the modern Clipboard API if available, otherwise fallback
        if (!navigator.clipboard || !navigator.clipboard.write) {
            // Replace the copy function with simpler version if Clipboard API not available
            window.copyContent = copyContentSimple;
        }
    </script>

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