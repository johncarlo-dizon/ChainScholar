<x-userlayout>
 
<x-header.bar
  title="Submitted Titles"
  subtitle="Research titles successfully submitted to the system"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

    <div class="container mx-auto px-4 mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- 🔍 Search + Filter -->
        <form method="GET" class="flex flex-wrap items-center gap-4 mb-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full sm:w-auto flex-1 border border-gray-300 rounded px-4 py-2 text-sm"
                   placeholder="Search by title...">
            <button type="submit"
                    class="px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                Search
            </button>
            <select name="status" onchange="this.form.submit()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
            </select>
        </form>

        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $titles->firstItem() ?? 0 }}</span>–
                <span class="font-medium">{{ $titles->lastItem() ?? 0 }}</span>
                of <span class="font-medium">{{ $titles->total() }}</span>
            </p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($titles->isEmpty())
                <div class="text-center py-12">
                    <h4 class="text-xl font-semibold mb-2">No submitted titles found</h4>
                    <p class="text-gray-600 mb-4">Submit a final document to move a title here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                       <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Authors</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Similarity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                    </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($titles as $title)
                               <tr>
  <td class="px-6 py-4 text-sm font-medium text-gray-900">
    {{ $title->title }}
  </td>

 <td class="px-6 py-4 text-sm text-gray-700">
  {{ $title->authors ?? '—' }}
</td>

  <td class="px-6 py-4 text-sm text-gray-500">
    {{ $title->submitted_at?->format('M d, Y h:i A') ?? '—' }}
  </td>

  {{-- Similarity column --}}
  <td class="px-6 py-4 text-sm">
    @if($title->finalDocument)
      @php
        $int = $title->finalDocument->plagiarism_internal;
        $ext = $title->finalDocument->plagiarism_external;

        $cls = function($v){
          if ($v === null) return 'bg-gray-100 text-gray-600';
          if ($v < 20)     return 'bg-green-100 text-green-800';
          if ($v < 40)     return 'bg-yellow-100 text-yellow-800';
          return            'bg-red-100 text-red-800';
        };
      @endphp

      <div class="flex items-center gap-2">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls($int) }}">
          Int: {{ is_null($int) ? '—' : number_format($int, 2) . '%' }}
        </span>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls($ext) }}">
          Ext: {{ is_null($ext) ? '—' : number_format($ext, 2) . '%' }}
        </span>
      </div>
    @else
      —
    @endif
  </td>

  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
    @if($title->finalDocument)
      <!-- Copy Content Button -->
      <button onclick="copyContent({{ $title->finalDocument->id }})" 
              class="text-blue-600 hover:text-blue-900 copy-btn"
              data-id="{{ $title->finalDocument->id }}"
              title="Copy document content to clipboard">
        Copy 
      </button>
      
      <!-- View Document Link -->
      <a href="{{ route('documents.view', ['id' => $title->finalDocument->id]) }}"
         class="text-indigo-600 hover:text-indigo-900">
        View 
      </a>

          <a href="{{ route('titles.plagiarism-certificate', $title->id) }}"
           class="text-green-600 hover:text-green-900"
           target="_blank">
            Plagiarism Cert 
        </a>
    @endif

    <!-- Cancel Submission Form -->
    <form method="POST" action="{{ route('titles.cancel', $title->id) }}" class="inline">
      @csrf
      @method('PATCH')
      <button type="submit" class="text-red-600 hover:text-red-900">
        Withdraw 
      </button>
    </form>
  </td>
</tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 🔽 Pagination -->
                <div class="px-6 py-4">
                    {{ $titles->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>

 

    <!-- Comment Modal -->
    <div id="commentModal" class="fixed inset-0 hidden backdrop-blur-sm bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-lg rounded shadow p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-800">Admin Comment</h3>
            <p id="commentContent" class="text-gray-700 whitespace-pre-line"></p>
            <div class="text-right mt-4">
                <button onclick="closeCommentModal()"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function openCommentModal(comment) {
            document.getElementById('commentContent').textContent = comment;
            document.getElementById('commentModal').classList.remove('hidden');
        }
        
        function closeCommentModal() {
            document.getElementById('commentModal').classList.add('hidden');
        }

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
</x-userlayout>