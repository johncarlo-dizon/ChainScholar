<x-userlayout>
 

  <x-header.bar
  title="Open Titles"
  subtitle="Available research titles awaiting adviser requests"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>
  <div class="container mx-auto px-4 py-6 space-y-4">
    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 grid gap-3 md:grid-cols-12 items-end">
      <!-- Search -->
      <div class="md:col-span-5">
        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <input id="search" name="search" value="{{ request('search') }}"
               placeholder="Search title, keywords, category..."
               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400" />
      </div>

      <!-- Status -->
      <div class="md:col-span-3">
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" name="status"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
          @php $s = request('status','all'); @endphp
          <option value="all" {{ $s==='all'?'selected':'' }}>All</option>
          <option value="verified" {{ $s==='verified'?'selected':'' }}>Verified (open)</option>
          <option value="awaiting_adviser" {{ $s==='awaiting_adviser'?'selected':'' }}>Awaiting adviser</option>
          <option value="awaiting_admin" {{ $s==='awaiting_admin'?'selected':'' }}>Waiting for admin</option>
        </select>
      </div>

      <!-- Assignment -->
      <div class="md:col-span-2">
        <label for="assign" class="block text-sm font-medium text-gray-700 mb-1">Assignment</label>
        @php $a = request('assign','all'); @endphp
        <select id="assign" name="assign"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
          <option value="all" {{ $a==='all'?'selected':'' }}>All</option>
          <option value="unassigned" {{ $a==='unassigned'?'selected':'' }}>No adviser yet</option>
          <option value="assigned" {{ $a==='assigned'?'selected':'' }}>Already assigned</option>
        </select>
      </div>

      <!-- Sort -->
      <div class="md:col-span-2">
        <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort by</label>
        @php $o = request('sort','verified'); @endphp
        <select id="sort" name="sort"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
          <option value="verified" {{ $o==='verified'?'selected':'' }}>Recently verified</option>
          <option value="title" {{ $o==='title'?'selected':'' }}>Title (A–Z)</option>
          <option value="owner" {{ $o==='owner'?'selected':'' }}>Owner (A–Z)</option>
        </select>
      </div>

      <!-- Actions -->
      <div class="md:col-span-12 flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Apply</button>
        <a href="{{ route('adviser.titles.browse') }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Reset</a>
      </div>
    </form>

    @forelse($titles as $t)
      <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <h3 class="font-semibold text-lg truncate">{{ $t->title }}</h3>
            <div class="text-sm text-gray-500">Owner: {{ $t->owner->name }}</div>

            @if($t->keywords)
              <div class="text-xs text-gray-500 mt-1 truncate">
                <span class="font-medium text-gray-600">Keywords:</span> {{ $t->keywords }}
              </div>
            @endif

           {{-- Chips --}}
@php
  $requestable      = is_null($t->primary_adviser_id) && in_array($t->status, ['verified','awaiting_adviser']);
  $sentPending      = !empty($t->has_my_pending_request);          // YOU → student (pending)
  $sentAccepted     = !empty($t->has_my_accepted_request);         // YOU → student (accepted)
  $studentInvited   = !empty($t->has_student_pending_invite);      // student → YOU (pending)
  $assignedNow      = !is_null($t->primary_adviser_id);
  $assignedToMe     = $assignedNow && ((int)$t->primary_adviser_id === (int)auth()->id());
@endphp


<div class="text-xs mt-2 flex flex-wrap gap-2">
  <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">{{ $t->status }}</span>

  @if($t->verified_at)
    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700">
      Verified: {{ $t->verified_at->format('M d, Y') }}
    </span>
  @endif

  {{-- show "Assigned to: ..." only if assigned to someone ELSE --}}
  @if($assignedNow && $t->status === 'awaiting_admin' && !$assignedToMe)
    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700">
      Assigned to: {{ optional($t->primaryAdviser)->name ?? 'another adviser' }}
    </span>
  @endif

  @if($sentPending)
    <span class="px-2 py-0.5 rounded bg-green-100 text-green-800">Request sent</span>
  @endif

  @if($sentAccepted)
    <span class="px-2 py-0.5 rounded bg-green-100 text-green-800">You were accepted</span>
  @endif
  @if($studentInvited)
  <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800">Student invited you</span>
@endif

</div>

          </div>

          {{-- Right-side action --}}
          <div class="shrink-0">
           @if($requestable && !$sentPending && !$sentAccepted && !$studentInvited)

              <form method="POST" action="{{ route('adviser.titles.request', $t) }}">
                @csrf
                <button class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                  Request to Advise
                </button>
              </form>
            @elseif($requestable && ($sentPending || $sentAccepted))
              <button class="px-4 py-2 bg-gray-200 text-gray-600 rounded cursor-not-allowed" disabled
                      title="{{ $sentAccepted ? 'Already accepted for you.' : 'You already sent a request.' }}">
                {{ $sentAccepted ? 'Already Accepted' : 'Request Sent' }}
              </button>
              @elseif($requestable && $studentInvited)
  <div class="flex items-center gap-2">
    <button class="px-4 py-2 bg-gray-200 text-gray-600 rounded cursor-not-allowed" disabled
            title="Student already invited you for this title. Review on Pending.">
      Student invited you
    </button>
    <a href="{{ route('adviser.requests.pending') }}"
      class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
      Review
    </a>
  </div>

            @else
              <button class="px-4 py-2 bg-gray-200 text-gray-600 rounded cursor-not-allowed" disabled
                    title="{{ $assignedNow ? ($assignedToMe ? 'You are the assigned adviser.' : 'Already assigned to another adviser.') : 'Not requestable right now.' }}">
            {{ $assignedNow ? ($assignedToMe ? 'Already Accepted' : 'Assigned') : 'Not Requestable' }}
            </button>

            @endif
          </div>
        </div>

        {{-- Description section with modal trigger --}}
        @if($t->description  )
          <div class="mt-4">
            <div class="text-sm text-gray-700">
              <div class="line-clamp-2">{{ Str::limit($t->description, 160, '…') }}</div>
              <button 
                type="button" 
                class="text-blue-600 hover:text-blue-800 text-xs font-medium mt-1 js-view-description"
                data-title="{{ $t->title }}"
                data-student="{{ $t->owner->name }}"
                data-description="{{ $t->description }}"
              >
                View full description →
              </button>
            </div>
          </div>
        @else
          <div class="mt-4 text-sm text-gray-400 italic">
            No description provided.
          </div>
        @endif
      </div>
    @empty
      <div class="text-gray-500">No titles found with the current filters.</div>
    @endforelse

    <div>{{ $titles->links() }}</div>
  </div>

 <!-- Description View Modal -->
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
        <div class="h-full overflow-y-auto p-4 text-gray-700 leading-relaxed text-sm whitespace-pre-wrap
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
      subtitleEl.textContent = `Owner: ${student}`;
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