<x-userlayout>
    <!-- Welcome Section -->
    <div class="bg-blue-600 rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-semibold text-white mb-2">Welcome to XXXXXXXXXXXXX</h2>
        <p class="text-gray-200">Your secure place and research tool for thesis and scholarly documents.</p>
    </div>

    <!-- Edit Button -->
    <div class="flex justify-end items-center mb-6">
        <a href="{{ route('documents.edit', $document) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            Edit Document
        </a>
    </div>

    <!-- Document Viewer Section -->
    <div class="container mx-auto bg-white rounded-lg shadow p-6">
        <!-- Document Header -->
        <div class="flex justify-center items-center border-b border-blue-500 pb-4 mb-6">
            <h1 class="text-3xl font-bold text-blue-800">{{ $document->title }}</h1>
        </div>

        <!-- Document Content -->
        <div class="document-content ck-content">
            {!! $document->content !!}
        </div>
    </div>

    <!-- Styles -->
    <style>
        /* Mimic CKEditor default content styling */
        .document-content {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
            padding: 20px;
            margin: 0 auto;
            max-width: 700px; /* similar to CKEditor editing area */
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .document-content p {
            margin-bottom: 1rem;
        }

        .document-content h1, 
        .document-content h2, 
        .document-content h3, 
        .document-content h4, 
        .document-content h5, 
        .document-content h6 {
            font-weight: bold;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .document-content ul, 
        .document-content ol {
            padding-left: 2rem;
            margin-bottom: 1rem;
        }

        .document-content blockquote {
            border-left: 4px solid #ccc;
            padding-left: 1rem;
            color: #666;
            margin: 1rem 0;
            font-style: italic;
        }

        .document-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 1rem 0;
        }

        .document-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .document-content table, 
        .document-content th, 
        .document-content td {
            border: 1px solid #ccc;
        }

        .document-content th, 
        .document-content td {
            padding: 0.5rem;
            text-align: left;
        }
    </style>
</x-userlayout>
