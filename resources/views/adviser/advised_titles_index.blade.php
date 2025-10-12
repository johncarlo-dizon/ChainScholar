<x-userlayout>
  
<x-header.bar
  title="Advised Titles"
  subtitle="Research titles under your guidance and supervision"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

    <div class="container mx-auto px-4 ">
      <!-- Search Bar (matches Research Library style) -->
<form method="GET" class="flex-1 max-w-2xl mb-4" role="search" aria-label="Advised titles search">
  @foreach(request()->except(['q','page']) as $k => $v)
    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
  @endforeach

  <div class="flex items-center rounded-lg border border-blue-200 bg-white shadow-sm focus-within:ring-2 focus-within:ring-blue-400">
    <input
      type="text"
      name="q"
      value="{{ request('q') }}"
      autocomplete="off"
      placeholder="Search by title or student name"
      class="w-full px-4 py-2 rounded-l-lg focus:outline-none text-gray-800 placeholder-gray-400 text-base"
      aria-label="Search by title or student name"
    />

    <button type="submit"
      class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-r-lg flex items-center justify-center">
      <i data-feather="search" class="w-5 h-5"></i>
    </button>
  </div>
</form>


        @if($titles->isEmpty())
            <div class="bg-white rounded-xl shadow p-8 text-center text-gray-600">
                You are not advising any titles yet.
            </div>
        @else
          <div class="space-y-3">
    @foreach($titles as $t)
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 hover:shadow-md transition p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $t->title }}
                    </div>
                    <div class="mt-1 text-sm text-gray-600">
                        Student: <span class="font-medium">{{ $t->owner->name }}</span>
                    </div>
                    
                    {{-- Description section with modal trigger --}}
                    @php
                      $description = trim((string)($t->description ?? ''));
                    @endphp
                    @if($description !== '')
                      <div class="mt-3">
                        <div class="text-sm text-gray-700">
                          <div class="line-clamp-2">{{ Str::limit($description, 160, '…') }}</div>
                          <button 
                            type="button" 
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-1 js-view-description"
                            data-title="{{ $t->title }}"
                            data-student="{{ $t->owner->name }}"
                            data-description="{{ $description }}"
                          >
                            View full description →
                          </button>
                        </div>
                      </div>
                    @else
                      <div class="mt-3 text-sm text-gray-400 italic">
                        No description provided.
                      </div>
                    @endif

                    {{-- Authors under the Student line --}}
                    @if(filled($t->authors))
                      @php
                        // Split comma-separated authors nicely into chips
                        $authorsList = collect(preg_split('/\s*,\s*/', (string)$t->authors, -1, PREG_SPLIT_NO_EMPTY));
                      @endphp

                      <div class="mt-3 text-xs text-gray-500">
                        Authors:
                      </div>
                      <div class="mt-0.5 flex flex-wrap gap-1.5">
                        @foreach($authorsList as $a)
                          <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px]">
                            {{ $a }}
                          </span>
                        @endforeach
                      </div>
                    @else
                      <div class="mt-3 text-xs text-gray-400">
                        Authors: —
                      </div>
                    @endif

                    <div class="mt-3 text-xs text-gray-500 flex gap-2 flex-wrap items-center">
                      @if($t->adviser_assigned_at)
                          <span>Assigned: {{ $t->adviser_assigned_at->format('M d, Y h:ia') }}</span>
                      @endif

                      @php
                          $statusChipClass = match($t->status) {
                              'awaiting_admin' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
                              'submitted', 'in_progress', 'approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                              default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
                          };

                          $finalDoc = $t->finalDocument;
                          $int = $finalDoc?->plagiarism_internal;
                          $ext = $finalDoc?->plagiarism_external;

                          $badge = function($v){
                              if ($v === null) return 'bg-gray-100 text-gray-600';
                              if ($v < 20)     return 'bg-green-100 text-green-800';
                              if ($v < 40)     return 'bg-yellow-100 text-yellow-800';
                              return            'bg-red-100 text-red-800';
                          };
                      @endphp

                      @if($t->status)
                          <span class="px-2 py-0.5 rounded-full {{ $statusChipClass }}">
                              {{ $t->status }}
                          </span>
                      @endif

                      {{-- If submitted (or a final doc exists), show similarity + final_document_id --}}
                      @if($t->status === 'submitted' || $finalDoc)
                          <span class="inline-flex items-center gap-1">
                              <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold {{ $badge($int) }}">
                                  Int: {{ is_null($int) ? '—' : number_format($int, 2).'%' }}
                              </span>
                              <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold {{ $badge($ext) }}">
                                  Ext: {{ is_null($ext) ? '—' : number_format($ext, 2).'%' }}
                              </span>
                          </span>

                          @if($finalDoc)
                              {{-- quick link to the final doc if you have a route --}}
                              <a href="{{ route('documents.view', ['id' => $finalDoc->id]) }}"
                                 class="text-indigo-600 hover:underline ml-1 font-medium text-sm">View</a>
                          @endif
                      @endif
                    </div>
                </div>

                @php
                  $lockedForAdviser = ($t->status === 'awaiting_admin');
                @endphp
                <div class="shrink-0 text-right">
                  @if(!$lockedForAdviser)
                      <a href="{{ route('adviser.advised.show', $t) }}"
                         class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                          View chapters
                          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                              <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                      </a>
                  @else
                      <span
                          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-300 text-gray-600 text-sm cursor-not-allowed opacity-70 select-none"
                          aria-disabled="true"
                          title="Waiting for admin approval">
                          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                              <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                              <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
                          </svg>
                          View chapters
                      </span>
                      <div class="text-xs text-gray-500 mt-1">
                          Waiting for admin approval.
                      </div>
                  @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

            <div class="mt-4">
                {{ $titles->links() }}
            </div>
        @endif
    </div>

   
<div id="description-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div id="description-overlay" class="absolute inset-0 bg-black/50"></div>
  <div class="relative bg-white rounded-xl shadow-lg max-w-2xl w-full mx-4 max-h-[80vh] flex flex-col z-10 overflow-hidden">
    <!-- Header - Fixed height -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
      <div class="min-w-0 pr-4">
        <h3 class="text-lg font-semibold text-gray-900 truncate" id="description-modal-title">Title Description</h3>
        <p class="text-sm text-gray-600 mt-1 truncate" id="description-modal-subtitle"></p>
      </div>
      <button type="button" id="description-close" class="text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0 p-1 rounded hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <!-- Content Area - Fixed height container -->
    <div class="p-6 flex-shrink-0" style="height: 400px;">
      <div class="h-full border border-gray-300 rounded-lg bg-white shadow-sm overflow-hidden">
        <div class="h-full overflow-y-auto p-4 text-gray-700 leading-relaxed text-sm
                    scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100
                    hover:scrollbar-thumb-gray-400"
             id="description-modal-content"
             tabindex="0"
             aria-label="Description content - scrollable area"
             role="textbox"
             aria-readonly="true">
          <!-- Content will be inserted here -->
        </div>
      </div>
    </div>
    
    <!-- Footer - Fixed height -->
    <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
      <button type="button" id="description-close-btn" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
        Close
      </button>
    </div>
  </div>
</div>

    <style>
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    </style>

    <script>
    // Description Modal functionality
    (function() {
      const modal = document.getElementById('description-modal');
      const overlay = document.getElementById('description-overlay');
      const closeBtn = document.getElementById('description-close');
      const closeBtn2 = document.getElementById('description-close-btn');
      const titleEl = document.getElementById('description-modal-title');
      const subtitleEl = document.getElementById('description-modal-subtitle');
      const contentEl = document.getElementById('description-modal-content');

      function openDescription(title, student, description) {
        titleEl.textContent = title;
        subtitleEl.textContent = `Student: ${student}`;
        contentEl.textContent = description;
        
        // Ensure modal is in body
        if (modal.parentElement !== document.body) {
          document.body.appendChild(modal);
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
      }

      function closeDescription() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = ''; // Restore scrolling
      }

      // Open via view description buttons
      document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-view-description');
        if (!btn) return;
        e.preventDefault();
        
        const title = btn.dataset.title || 'Untitled';
        const student = btn.dataset.student || 'Student';
        const description = btn.dataset.description || 'No description available.';
        
        openDescription(title, student, description);
      });

      // Close handlers
      closeBtn && closeBtn.addEventListener('click', closeDescription);
      closeBtn2 && closeBtn2.addEventListener('click', closeDescription);
      overlay && overlay.addEventListener('click', closeDescription);
      
      // ESC key close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
          closeDescription();
        }
      });
    })();
    </script>
</x-userlayout>