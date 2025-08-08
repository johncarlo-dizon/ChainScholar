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

    <!-- Default Sidebar Panel -->
    <div id="default-sidebar" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full space-y-8">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700">Document Info</h3>
            </div>
            <div id="editor-word-count" class="text-sm text-gray-500">
                <!-- Word count will be injected here -->
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700">Actions</h3>
            </div>
            <div class="space-y-4">
                <div class="flex flex-col">
                    <a href="{{ route('templates.index', ['use_for' => 'chapter', 'document_id' => $document->id]) }}"
                       class="text-sm text-blue-500 transition hover:text-blue-700">
                        Use Template
                    </a>

                    @if(session()->has('templateContent') && !session()->has('templateUndone'))
                        <a href="{{ route('documents.undoTemplate', $document) }}"
                           class="text-sm text-blue-500 transition hover:text-blue-700">
                            Undo Template
                        </a>
                    @endif

                    <a href="javascript:void(0);" onclick="toggleSubmitForm()"
                       class="text-sm text-blue-500 transition hover:text-blue-700">
                        Submit Document for Approval
                    </a>
                </div>
            </div>
        </div>



           <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700">Plagiarism Checker</h3>  

            </div>
            <div class="space-y-4">
                <div class="flex flex-col">
                   

<div id="plagiarism-result" class="text-sm  text-gray-700 hidden"></div>
              
                </div>
            </div>
        </div>














        <div class="pt-2 border-t border-gray-200">
            <div class="flex justify-between">
                <a href="{{ route('titles.chapters', $document->title_id) }}"
                   class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition duration-200">
                    Back to Chapters
                </a>
                <button type="submit"      id="saveBtn"
                        class="flex-1 text-center ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-sm focus:ring-2 focus:ring-blue-300">
                    Save
                </button>
            </div>
        </div>
    </div>
      </form>

    <!-- Submit Final Document Form Panel -->
    <div id="submit-sidebar" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
        <form action="{{ route('documents.submit', ['title_id' => $document->title_id]) }}" method="POST" enctype="multipart/form-data" id="submit-final-form">
            @csrf
    <input type="hidden" name="finaldocument_id" value="{{ $document->id }}">
   


      <div class="space-y-5">


    <!-- Authors -->
    <div>
        <label class="block text-gray-700 font-medium mb-1">Authors <span class="text-sm text-gray-500">(comma-separated)</span></label>
        <input type="text" name="authors" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            placeholder="e.g., Juan Dela Cruz, Maria Santos">
    </div>


    <!-- Abstract -->
    <div>
        <label class="block text-gray-700 font-medium mb-1">Abstract</label>
        <textarea name="abstract" rows="4" required
            class="w-full border border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
            placeholder="Enter a concise summary of your research..."></textarea>
    </div>

 

    <!-- Research Type -->
    <div>
        <label class="block text-gray-700 font-medium mb-1">Research Type</label>
        <select name="research_type" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white">
            <option value="" disabled selected>Select type...</option>
            <option value="Capstone">Capstone</option>
            <option value="Thesis">Thesis</option>
            <option value="Journal">Journal</option>
            <option value="Funded">Funded</option>
            <option value="Independent">Independent</option>
        </select>
    </div>
</div>


            <textarea name="final_content" id="finalContent" class="hidden"></textarea>

           <div class="pt-2 border-t border-gray-200">
             <div class="flex justify-between">
                <button type="button" onclick="toggleSubmitForm()"  class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition duration-200">
                    ← Cancel
                </button>
                <button type="submit"
                  id="submitBtn"
 
                        onclick="prepareFinalContent()"
               class="flex-1 text-center ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-sm focus:ring-2 focus:ring-blue-300">
                    Submit Final Document
                </button>
            </div>
           </div>


  



        </form>
    </div>

</div>


    <!-- CKEditor Scripts -->
    <link rel="stylesheet" href="{{ asset('assets/editor.css') }}">
    <script src="{{ asset('assets/editor.js') }}"></script>

    <script>
        window.addEventListener("beforeunload", function () {
            fetch("{{ route('clear.template.session') }}");
        });


function prepareFinalContent() {
    const contentElement = document.querySelector('.ck-content');
    if (!contentElement) {
        alert("Editor content not found.");
        return false;
    }

    // Clone the content to avoid modifying the DOM directly
    const cloned = contentElement.cloneNode(true);

    // Remove any placeholder attributes
    cloned.querySelectorAll('[data-placeholder]').forEach(el => {
        el.removeAttribute('data-placeholder');
    });

    // Clean HTML to submit
    const cleanHtml = cloned.innerHTML.trim();

    // Set to hidden field
    document.getElementById('finalContent').value = cleanHtml;

    return true;
}


    function toggleSubmitForm() {
        const defaultSidebar = document.getElementById('default-sidebar');
        const submitSidebar = document.getElementById('submit-sidebar');

        defaultSidebar.classList.toggle('hidden');
        submitSidebar.classList.toggle('hidden');
    }
</script>



<script>
// ---------- config ----------
const THRESHOLD = 30;
const MIN_CHARS_TO_CHECK = 40; // avoid noise when the doc is still empty

// ---------- helpers ----------
const saveBtn = document.getElementById('saveBtn');
const submitBtn = document.getElementById('submitBtn');
const resultBox = document.getElementById('plagiarism-result');

function setButtonsDisabled(disabled, reason = '') {
    [saveBtn, submitBtn].forEach(btn => {
        if (!btn) return;
        btn.disabled = disabled;
        btn.classList.toggle('opacity-50', disabled);
        btn.classList.toggle('cursor-not-allowed', disabled);
        btn.classList.toggle('hover:bg-blue-700', !disabled);
    });
    if (reason && resultBox) {
        resultBox.classList.remove('hidden');
        resultBox.innerHTML = `<span class="text-gray-600">${reason}</span>`;
    }
}

function getEditorText() {
    const contentElement = document.querySelector('.ck-content');
    if (!contentElement) return null;
    return contentElement.innerText.trim();
}

// ---------- run check ----------
async function checkPlagiarism() {
    const text = getEditorText();
    if (text === null) {
        setButtonsDisabled(true, 'Editor not ready yet…');
        return;
    }
    if (text.length < MIN_CHARS_TO_CHECK) {
        setButtonsDisabled(true, 'Type more content, then run the checker.');
        resultBox.classList.remove('hidden');
        resultBox.innerHTML = `<span class="text-gray-600">Waiting for more content…</span>`;
        return;
    }

    resultBox.classList.remove('hidden');
    resultBox.innerHTML = `
        <div class="flex items-center space-x-2 text-gray-600">
            <svg class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Checking for plagiarism...</span>
        </div>
    `;

    try {
        const response = await fetch("{{ route('documents.checkPlagiarismLive') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                content: text,
                document_id: {{ $document->id }}
            })
        });

        const data = await response.json();
        const score = Number(data.score ?? 0);

        resultBox.innerHTML = `<span>Plagiarism Score:</span> ${score}%`;

        if (score >= THRESHOLD) {
            resultBox.innerHTML += `<br><span class="text-red-600">High similarity detected! ⚠️</span>`;
            setButtonsDisabled(true);
        } else {
            resultBox.innerHTML += `<br><span class="text-green-600">Content appears original ✅</span>`;
            setButtonsDisabled(false);
        }
    } catch (error) {
        setButtonsDisabled(true, 'Error checking plagiarism. Please try again.');
    }
}

// ---------- init: disable on load, then auto-run when editor is ready ----------
document.addEventListener('DOMContentLoaded', () => {
    setButtonsDisabled(true, 'Run the plagiarism checker to enable Save & Submit.');

    // Wait for CKEditor .ck-content to exist, then auto-run once
    const waitForEditor = setInterval(() => {
        const el = document.querySelector('.ck-content');
        if (el) {
            clearInterval(waitForEditor);
            // Auto-run once the editor is ready
            setTimeout(checkPlagiarism, 300);

            // Re-disable when user types; debounce re-check
            let debounce;
            const observer = new MutationObserver(() => {
                setButtonsDisabled(true, 'Content changed. Please run the checker again.');
                clearTimeout(debounce);
                // Optional: auto re-check after user stops typing for 1s
                // comment out the next line if you want manual "Run" only
                debounce = setTimeout(checkPlagiarism, 1000);
            });
            observer.observe(el, { subtree: true, characterData: true, childList: true });
        }
    }, 150);
});

// Expose to the Run button
window.checkPlagiarism = checkPlagiarism;
</script>





    <style>
        .ck-content {
            min-height: 600px;
            background-color: white;
            color: #000;
            padding: 1.5rem !important;
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
