<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl text-white font-semibold mb-4">{{ isset($document) ? 'Edit' : 'New' }} Document</h2>
    </div>

    <div class="container mx-auto px-4 py-8">
       <form action="{{ isset($document) && !isset($template) ? route('documents.update', $document) : route('documents.store') }}" method="POST" class="document-form" id="doc-form">

            @csrf
            @if(isset($document))
                @method('PUT')
            @endif

            <div class="flex flex-col lg:flex-row gap-6">
                <div class="main-container">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <div class="mb-6">
                                <label for="title" class="block mb-2 font-bold text-blue-600">Document Title</label>
                                <input 
                                    type="text" 
                                    name="title" 
                                    id="title"
                                    class="w-full border-1 border-gray-300 rounded-md px-4 py-3 text-lg focus:outline-none focus:ring-1 focus:ring-blue-400"
                                    placeholder="Enter document title" 
                                    value="{{ old('title', $document->title ?? $template->title ?? '') }}"   
                                >

                            </div>

                            <div class="main-container">
                                <label for="editor" class="block mb-2 font-bold text-blue-600">Document Content</label>

                            	<div
				class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-word-count editor-container_include-fullscreen"
				id="editor-container"
                
			>

                                <!-- Textarea -->
                              	<div class="editor-container__editor">  <textarea 
    name="content" 
    id="editor" 
    class="min-h-[600px] w-full p-4 bg-white border border-gray-300 rounded-md hidden"
>{{ old('content', $document->content ?? $template->content ?? '') }}</textarea>
</div>

                                <!-- Word Count Container -->
                   
                            </div>
    </div>

				



                      
                        </div>
                    </div>
                </div>



    
<div class="w-full lg:w-1/3 lg:sticky lg:top-6 h-fit">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8 h-120 flex flex-col justify-between">

        <!-- Section: Document Summary -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700">Document Info</h3>           
            </div>
            <div id="editor-word-count" class="text-sm text-gray-500">
                <!-- Word count will be injected here -->
            </div>


            <div class="flex items-center justify-between ">
                <h3 class="text-lg font-semibold text-gray-700 ">Title Verification</h3>           
            </div>
         <div class="mb-4 flex flex-col space-y-2">

 
            <div class="text-sm text-gray-600 flex">
             <div>
              Internal Scan: &nbsp; 
             </div>
             <div id="similarity-result" class="text-sm text-gray-600"> Waiting for title input...</div>
            </div>
          
 

            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="similarity-bar" class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
            </div>
            <span id="similarity-percent" class="text-xs text-gray-500">0%</span>

       
            <div class="text-sm text-gray-600 flex">
                        <div>
                    Web Scan: &nbsp; 
                        </div>
               <p id="external-similarity-result" class="text-sm text-gray-600"> Waiting for title input...</p>
            </div>



            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="external-similarity-bar" class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
            </div>
            <span id="external-similarity-percent" class="text-xs text-gray-500">0%</span>
        </div>



 

        </div>

 

       

        <!-- Section: Actions -->
        <div class="pt-2 border-t border-gray-200">
            <div class="flex justify-between">
                <a href="{{ route('documents.index') }}"
                   class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition duration-200 {{ isset($document) ? 'block' : 'hidden' }} ">
                    Cancel
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

    <!-- Load CKEditor assets -->
    <link rel="stylesheet" href="{{ asset('assets/editor.css') }}">
    <script src="{{ asset('assets/editor.js') }}"></script>


        
<script>
const currentDocumentId = "{{ isset($document) ? $document->id : '' }}";
    // Set default message on page load
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById("similarity-result").innerText = "  Waiting for title input...";
        document.getElementById("external-similarity-result").innerText = " Waiting for title input...";
        document.getElementById("similarity-bar").style.width = "0%";
        document.getElementById("similarity-percent").innerText = "0%";
    });

   function checkSimilarity(title) {
    const resultText = document.getElementById("similarity-result");
    const similarityBar = document.getElementById("similarity-bar");
    const similarityPercent = document.getElementById("similarity-percent");

    if (title.length < 5) {
        resultText.innerText = "Enter at least 5 characters to begin checking.";
        similarityBar.style.width = "0%";
        similarityPercent.innerText = "0%";

        const externalResultText = document.getElementById("external-similarity-result");
        const externalBar = document.getElementById("external-similarity-bar");
        const externalPercent = document.getElementById("external-similarity-percent");

        externalResultText.innerText = "Enter at least 5 characters to begin checking.";
        externalBar.style.width = "0%";
        externalPercent.innerText = "0%";

        return;
    }


    resultText.innerText = "Checking similarity...";
    similarityBar.style.width = "0%";
    similarityPercent.innerText = "Loading...";
    document.getElementById("external-similarity-result").innerText = "Checking similarity...";

    // 🔵 INTERNAL CHECK
   fetch("{{ route('documents.check-similarity') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            title: title, 
            document_id: currentDocumentId || null 
        })
    })
    .then(res => res.json())
    .then(data => {
        const percent = (data.max_similarity ?? 0).toFixed(2);
        similarityBar.style.width = percent + "%";
        similarityPercent.innerText = percent + "%";

        let message = `Similarity: ${percent}%`;
        if (!data.approved) {
            message += ` — Title might be rejected.`;
            resultText.classList.remove("text-gray-600");
            resultText.classList.add("text-red-600");
        } else {
            message += ` — Title is acceptable.`;
            resultText.classList.remove("text-red-600");
            resultText.classList.add("text-green-600");
        }

        resultText.innerText = message;
    })
    .catch(err => {
        console.error('Fetch error:', err);
        resultText.innerText = "Error checking similarity.";
        similarityBar.style.width = "0%";
        similarityPercent.innerText = "0%";
    });

    checkWebSimilarity(title);
}

function checkWebSimilarity(title) {
    const externalResultText = document.getElementById("external-similarity-result");
    const externalBar = document.getElementById("external-similarity-bar");
    const externalPercent = document.getElementById("external-similarity-percent");


    
    externalResultText.innerText = "Checking similarity...";
    externalBar.style.width = "0%";
    externalPercent.innerText = "Loading...";

    fetch("{{ route('documents.check-web') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            title: title,
            document_id: currentDocumentId || null
        })

    })
    .then(res => res.json())
    .then(data => {
        const percent = (data.max_similarity ?? 0).toFixed(2);
        externalBar.style.width = percent + "%";
        externalPercent.innerText = percent + "%";

        let message = `Similarity: ${percent}%`;
        if (!data.approved) {
            message += ` — Title might be rejected.`;
            externalResultText.classList.remove("text-gray-600");
            externalResultText.classList.add("text-red-600");
        } else {
            message += ` — Title is acceptable.`;
            externalResultText.classList.remove("text-red-600");
            externalResultText.classList.add("text-green-600");
        }

        externalResultText.innerText = message;
    })
    .catch(err => {
        console.error('Error fetching web similarity:', err);
        externalResultText.innerText = "Error checking similarity.";
        externalBar.style.width = "0%";
        externalPercent.innerText = "0%";
    });
}

function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

const debouncedCheckSimilarity = debounce(checkSimilarity, 600);

document.addEventListener('DOMContentLoaded', () => {
    const titleInput = document.getElementById("title");
    if (titleInput) {
        titleInput.addEventListener("input", (e) => {
            debouncedCheckSimilarity(e.target.value);
        });
    }
});
</script>


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
