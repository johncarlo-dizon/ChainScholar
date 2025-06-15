<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl font-semibold mb-4 text-white">{{ isset($document) ? 'Edit' : 'New' }} Template</h2>
    </div>

    <div class="container mx-auto px-4 py-8">
        <form action="{{ isset($document) ? route('templates.update', $document) : route('templates.store') }}" method="POST" class="document-form" id="doc-form">
            @csrf
            @if(isset($document))
                @method('PUT')
            @endif

            <div class="flex flex-col lg:flex-row gap-6">
                <div class="main-container">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <div class="mb-6">
                                <label for="title" class="block mb-2 font-bold text-blue-600">Template Title</label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name"
                                    class="w-full border-1 border-gray-300 rounded-md px-4 py-3 text-lg focus:outline-none focus:ring-1 focus:ring-blue-400"
                                    placeholder="Enter template title" 
                                    value="{{ old('name', $document->name ?? '') }}"
                                >
                            </div>
                            

                            <div class="main-container">
                                <label for="editor" class="block mb-2 font-bold text-blue-600">Template Content</label>

                          	<div
				class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-word-count editor-container_include-fullscreen"
				id="editor-container"
			>

                                <!-- Textarea -->
                              	<div class="editor-container__editor">  <textarea 
                                    name="content" 
                                    id="editor" 
                                    class="min-h-[600px] w-full p-4 bg-white border border-gray-300 rounded-md hidden"
                                >{{ old('content', $document->content ?? '') }}</textarea></div>

                                <!-- Word Count Container -->
                   
                            </div>
    </div>


		




                      
                        </div>
                    </div>
                </div>



    
<div class="w-full lg:w-1/3 lg:sticky lg:top-6 h-fit">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8 h-75 flex flex-col justify-between">

        <!-- Section: Document Summary -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700">Template Info</h3>           
            </div>
            <div id="editor-word-count" class="text-sm text-gray-500">
                <!-- Word count will be injected here -->
            </div>

            <div class="flex items-center justify-between hidden">
                <h3 class="text-lg font-semibold text-gray-700 hidden">Features</h3>           
            </div>
            <div class="mb-4 flex items-center space-x-2 hidden">
                <input type="checkbox" id="enable-page-break" class="toggle-page-break border-gray-300" />
                <label for="enable-page-break" class="text-sm text-gray-500">Enable Page Break (8.5x11 Short Paper)</label>
            </div>

        </div>

 

       

        <!-- Section: Actions -->
        <div class="pt-6 border-t border-gray-200">
            <div class="flex justify-between">
                <a href="{{ route('templates.index') }}"
                   class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition duration-200">
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

    <style>
        .ck-content {
            min-height: 600px;
            background-color: white;
            color: #000; 
        }
        .ck-content ul,
        .ck-content ol {
            padding-left: 2rem; 
            list-style: disc; 
        }

        .ck-content ol {
            list-style: decimal;
        }

        .ck-content li {
            margin-bottom: 0.3em;
        }
        .ck-powered-by{
            display: none !important;
        }

        /* STICKY */
        .ck.ck-toolbar {
            position: sticky !important;
            top: 6rem; /* adjust this value if you have a top navbar */
            z-index: 100;
            background-color: white;
        }

 
    </style>
</x-userlayout>
