<x-reslayout>
    <!-- Welcome Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-semibold mb-2">Welcome to ChainScholar</h2>
        <p class="text-gray-600">Your secure place and research tool for thesis and scholarly documents.</p>
    </div>

    <!-- Document Viewer Section -->
    <div class="container mx-auto bg-white rounded-lg shadow p-6">
        <!-- Document Header -->
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <h1 class="text-3xl font-bold text-gray-800">{{ $document->title }}</h1>
            <a href="{{ route('documents.edit', $document) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Edit Document
            </a>
        </div>

        <!-- Document Content -->
        <div class="document-content prose max-w-none">
            {!! $document->content !!}
        </div>
    </div>

    <!-- Styles -->
    <style>
        .document-content {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            min-height: 80vh;
            color: #1a1a1a;
        }

        .document-content p {
            margin-bottom: 1rem;
        }

        .document-content h1, 
        .document-content h2, 
        .document-content h3 {
            font-weight: bold;
        }

        .document-content ul, 
        .document-content ol {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }
    </style>
</x-reslayout>
