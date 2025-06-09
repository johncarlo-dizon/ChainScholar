<x-reslayout>
 
  <div class="bg-white rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl font-semibold mb-4">           {{ isset($document) ? 'Edit' : 'New' }} Document</h2>
  </div>

<div class=" mx-auto px-4 py-8  ">
    <form action="{{ isset($document) ? route('documents.update', $document) : route('documents.store') }}" method="POST" class="document-form">
        @csrf
        @if(isset($document))
            @method('PUT')
        @endif

        <div class=" flex flex-col lg:flex-row -mx-4">
            <div class="lg:w-3/3 px-4">
                <div class="bg-white rounded-lg shadow">
                
                    <div class="p-6">
                        <div class="mb-6">
                            <label for="title" class="block mb-2 font-bold text-blue-600">Document Title</label>
                            <input 
                                type="text" 
                                name="title" 
                                id="title"
                                class="w-full border-2 border-blue-600 rounded-md px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                                placeholder="Enter document title" 
                                value="{{ old('title', $document->title ?? '') }}"
                            >
                        </div>

                        <div class="mb-6">
                            <label for="editor" class="block mb-2 font-bold text-blue-600">Document Content</label>
                            <div class="editor-container border border-gray-300 rounded-md">
                                <div id="toolbar-container" class="p-2 border-b border-gray-300"></div>
                                <div 
                                    id="editor" 
                                    class="min-h-[400px] p-4"
                                >{!! old('content', $document->content ?? '') !!}</div>
                                <!-- Hidden textarea to submit editor content -->
                                <textarea name="content" id="hidden-content" class="hidden"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-between mt-8">
                            <a href="{{ route('documents.index') }}" 
                               class="inline-block px-6 py-3 border border-gray-400 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Save Document
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


  <!-- CKEditor Script -->
  <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/decoupled-document/ckeditor.js"></script>
  <script>
      let editorInstance;

      const form = document.querySelector('.document-form');
      const editorElement = form.querySelector('#editor');
      const toolbarContainer = form.querySelector('#toolbar-container');
      const hiddenContent = form.querySelector('#hidden-content');

      DecoupledEditor
          .create(editorElement, {
              fontSize: {
                  options: ['8pt', '10pt', '12pt', '14pt', '18pt', '24pt', '36pt']
              },
              fontFamily: {
                  options: ['Arial, sans-serif', 'Georgia, serif', 'Courier New, monospace', 'Comic Sans MS, cursive']
              },
              toolbar: {
                  items: [
                      'heading', '|',
                      'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                      'bold', 'italic', 'underline', 'strikethrough', '|',
                      'alignment', '|',
                      'numberedList', 'bulletedList', '|',
                      'outdent', 'indent', '|',
                      'link', 'imageUpload', 'blockQuote', 'insertTable', '|',
                      'undo', 'redo'
                  ],
                  shouldNotGroupWhenFull: true
              },
              simpleUpload: {
                  uploadUrl: '{{ route("upload.image") }}',
                  headers: {
                      'X-CSRF-TOKEN': '{{ csrf_token() }}'
                  }
              }
          })
          .then(editor => {
              toolbarContainer.appendChild(editor.ui.view.toolbar.element);
              editorInstance = editor;
          })
          .catch(error => {
              console.error('CKEditor error:', error);
          });

      // Before submitting the isolated form, set hidden textarea with editor content
      form.addEventListener('submit', function (e) {
          hiddenContent.value = editorInstance.getData();
      });
  </script>


<style>
  :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f4 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
 
        }
        
        .header {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 0 0 20px 20px;
        }
        
        .container-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 2rem;
            background: white;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: 1.2rem 1.5rem;
            border: none;
            font-weight: 600;
        }
        
        .editor-container {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 10px 15px;
        }
        
        .editor-content {
            min-height: 500px;
            padding: 1.5rem;
            background-color: white;
            border: 1px solid #e9ecef;
            border-top: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.4);
        }
        
        .btn-outline-secondary {
            padding: 10px 25px;
            font-weight: 600;
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            height: 100%;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .font-size-demo {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 20px 0;
        }
        
        .size-demo {
            padding: 8px 15px;
            border-radius: 6px;
            background: rgba(67, 97, 238, 0.08);
            border-left: 4px solid var(--primary);
        }
        
        .size-label {
            font-weight: 600;
            color: var(--secondary);
            margin-right: 10px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-enabled {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }
        
        .status-disabled {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .font-sample {
            font-family: 'Georgia', serif;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px dashed #dee2e6;
            margin: 25px 0;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .footer {
            text-align: center;
            padding: 2rem 0;
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 3rem;
        }
        
        .note-box {
            background: #fff9db;
            border-left: 4px solid #ffd43b;
            padding: 15px;
            border-radius: 0 6px 6px 0;
            margin: 25px 0;
        }
</style>
</x-reslayout>