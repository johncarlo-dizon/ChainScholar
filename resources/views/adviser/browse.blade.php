<x-userlayout>
  <x-header.bar
    title="Open Titles"
    subtitle="Available research titles awaiting adviser requests"
    :unread-count="$unreadCount ?? 0"
    :notifications="$notifications ?? collect()"
    :user="Auth::user()"
  />
  
  <div class="container mx-auto px-4 space-y-4">
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
      @php
        $requestable = is_null($t->primary_adviser_id) && in_array($t->status, ['verified','awaiting_adviser']);
        $sentPending = !empty($t->has_my_pending_request);
        $sentAccepted = !empty($t->has_my_accepted_request);
        $studentInvited = !empty($t->has_student_pending_invite);
        $assignedNow = !is_null($t->primary_adviser_id);
        $assignedToMe = $assignedNow && ((int)$t->primary_adviser_id === (int)auth()->id());
        
        // Get the adviser request for cancel action
        $adviserRequest = $t->adviserRequests->firstWhere('requested_by', 'adviser');
      @endphp

      <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0 flex-1">
            <h3 class="font-semibold text-lg text-gray-900 truncate">{{ $t->title }}</h3>
            <div class="text-sm text-gray-600 mt-1">Owner: {{ $t->owner->name }}</div>

            @if($t->keywords)
              <div class="text-xs text-gray-500 mt-1 truncate">
                <span class="font-medium text-gray-600">Keywords:</span> {{ $t->keywords }}
              </div>
            @endif

            <!-- Consolidated Status Badge -->
            <div class="mt-2 flex flex-wrap items-center gap-2">
              @if($sentAccepted)
                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 border border-green-200">
                  ✓ Accepted
                </span>
              @elseif($sentPending)
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 border border-blue-200">
                  ⏳ Request Sent
                </span>
              @elseif($studentInvited)
                <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800 border border-purple-200">
                  📨 Invitation Received
                </span>
              @elseif($assignedNow && $assignedToMe)
                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 border border-green-200">
                  ✓ Assigned to You
                </span>
              @elseif($assignedNow && !$assignedToMe)
                <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600 border border-gray-300">
                  👤 Assigned
                </span>
              @else
                <span class="px-2 py-1 rounded-full text-xs bg-indigo-100 text-indigo-700 border border-indigo-200 capitalize">
                  {{ str_replace('_', ' ', $t->status) }}
                </span>
              @endif

              @if($t->verified_at)
                <span class="text-xs text-gray-500">
                  Verified {{ $t->verified_at->format('M d, Y') }}
                </span>
              @endif
            </div>
          </div>

          <!-- Right-side Actions - Compact and Consistent -->
          <div class="shrink-0 flex flex-col gap-2 min-w-[140px]">
            @if($requestable && !$sentPending && !$sentAccepted && !$studentInvited)
              <form method="POST" action="{{ route('adviser.titles.request', $t) }}" class="w-full">
                @csrf
                <button type="submit" 
                        class="w-full px-3 py-2 bg-green-500 text-white text-sm rounded-md hover:bg-green-600 transition-colors font-medium">
                  Request to Advise
                </button>
              </form>
            @elseif($requestable && $sentPending)
              <div class="flex flex-col gap-2">
                <button class="w-full px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-md cursor-not-allowed" disabled>
                  Request Sent
                </button>
                @if($adviserRequest)
                  <form method="POST" action="{{ route('adviser.requests.cancel', $adviserRequest) }}" 
                        id="cancelForm-{{ $t->id }}" class="w-full">
                    @csrf
                    <button type="button"
                            class="w-full px-3 py-1.5 bg-blue-500 text-white text-xs rounded-md hover:bg-blue-600 transition-colors"
                            data-confirm
                            data-title="Cancel Request"
                            data-message="Cancel your request to advise &quot;{{ $t->title }}&quot;?"
                            data-form="cancelForm-{{ $t->id }}">
                      Cancel Request
                    </button>
                  </form>
                @endif
              </div>
            @elseif($requestable && $studentInvited)
              <div class="flex flex-col gap-2">
                <button class="w-full px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-md cursor-not-allowed" disabled>
                  Invitation Received
                </button>
                <a href="{{ route('adviser.requests.pending') }}"
                   class="w-full px-3 py-2 bg-blue-500 text-white text-sm rounded-md hover:bg-blue-600 transition-colors text-center font-medium">
                  Review
                </a>
              </div>
            @elseif($sentAccepted)
              <button class="w-full px-3 py-2 bg-green-100 text-green-700 text-sm rounded-md cursor-not-allowed border border-green-200" disabled>
                ✓ Accepted
              </button>
            @else
              <button class="w-full px-3 py-2 bg-gray-100 text-gray-500 text-sm rounded-md cursor-not-allowed" disabled
                      title="{{ $assignedNow ? ($assignedToMe ? 'You are the assigned adviser.' : 'Already assigned to another adviser.') : 'Not requestable right now.' }}">
                {{ $assignedNow ? ($assignedToMe ? 'Assigned to You' : 'Already Assigned') : 'Not Available' }}
              </button>
            @endif
          </div>
        </div>

        <!-- Description Section -->
        @if($t->description)
          <div class="mt-4 pt-4 border-t border-gray-100">
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
          <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-400 italic">
            No description provided.
          </div>
        @endif
      </div>
    @empty
      <div class="bg-white rounded-xl shadow p-8 text-center">
        <div class="text-gray-500 text-lg">No titles found with the current filters.</div>
        <p class="text-gray-400 mt-2">Try adjusting your search criteria</p>
      </div>
    @endforelse

    <div class="mt-6">
      {{ $titles->links() }}
    </div>
  </div>

  <!-- Description View Modal -->
  <div id="description-modal" class="fixed inset-0 hidden items-center justify-center z-50">
    <div id="description-overlay" class="absolute inset-0 bg-black/50"></div>
    <div class="relative bg-white rounded-xl shadow-lg max-w-2xl w-full mx-4 max-h-[80vh] flex flex-col z-10 overflow-hidden">
      <!-- Header -->
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
      
      <!-- Content Area -->
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
      
      <!-- Footer -->
      <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
        <button type="button" id="description-close-btn" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
          Close
        </button>
      </div>
    </div>
  </div>

  <!-- Confirm Modal (Fixed Version) -->
  <div id="confirmModal" class="fixed inset-0 hidden items-center justify-center z-[60]">
    <div id="confirmOverlay" class="absolute inset-0 backdrop-blur-sm bg-black/20"></div>
    <div class="relative bg-white rounded-xl shadow-xl p-6 max-w-lg w-full mx-4 z-10">
      <div class="flex items-start gap-3">
        <div class="shrink-0 w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
          <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="min-w-0">
          <h3 id="confirmTitle" class="text-lg font-semibold text-gray-900">Confirm</h3>
          <p id="confirmMessage" class="mt-1 text-sm text-gray-600">Are you sure?</p>
        </div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button type="button" id="confirmCancelBtn"
                class="px-3 py-1.5 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm">Cancel</button>
        <button type="button" id="confirmOkBtn"
                class="px-3 py-1.5 rounded bg-blue-600 text-white hover:bg-blue-700 text-sm">Confirm</button>
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
    // Check if modals are already initialized to prevent duplicates
    if (!window.browsePageModalsInitialized) {
      window.browsePageModalsInitialized = true;
      
      // Description Modal functionality
      (function() {
        const modal = document.getElementById('description-modal');
        const overlay = document.getElementById('description-overlay');
        const closeBtn = document.getElementById('description-close');
        const closeBtn2 = document.getElementById('description-close-btn');
        const titleEl = document.getElementById('description-modal-title');
        const subtitleEl = document.getElementById('description-modal-subtitle');
        const contentEl = document.getElementById('description-modal-content');

        // Check if elements exist
        if (!modal || !overlay) {
          console.log('Description modal elements not found');
          return;
        }

        function openDescription(title, student, description) {
          titleEl.textContent = title;
          subtitleEl.textContent = `Owner: ${student}`;
          contentEl.textContent = description;
          
          modal.classList.remove('hidden');
          modal.classList.add('flex');
          document.body.style.overflow = 'hidden';
        }

        function closeDescription() {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          document.body.style.overflow = '';
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
        if (closeBtn) closeBtn.addEventListener('click', closeDescription);
        if (closeBtn2) closeBtn2.addEventListener('click', closeDescription);
        if (overlay) overlay.addEventListener('click', closeDescription);
        
        // ESC key close
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeDescription();
          }
        });
      })();

      // Confirm Modal functionality
      (function () {
        const modal = document.getElementById('confirmModal');
        const overlay = document.getElementById('confirmOverlay');
        const titleEl = document.getElementById('confirmTitle');
        const msgEl = document.getElementById('confirmMessage');
        const okBtn = document.getElementById('confirmOkBtn');
        const cancelBtn = document.getElementById('confirmCancelBtn');
        
        // Check if elements exist
        if (!modal || !overlay || !okBtn || !cancelBtn) {
          console.log('Confirm modal elements not found:', { modal, overlay, okBtn, cancelBtn });
          return;
        }

        let targetFormId = null;

        function openModal({ title, message, formId }) {
          console.log('Opening confirm modal for form:', formId);
          titleEl.textContent = title || 'Confirm';
          msgEl.textContent = message || 'Are you sure?';
          targetFormId = formId || null;
          modal.classList.remove('hidden');
          modal.classList.add('flex');
          document.body.style.overflow = 'hidden';
        }

        function closeModal() {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          document.body.style.overflow = '';
          targetFormId = null;
        }

        // Event delegation for confirm buttons
        document.addEventListener('click', (e) => {
          const btn = e.target.closest('[data-confirm]');
          if (!btn) return;
          
          const formId = btn.getAttribute('data-form');
          const form = document.getElementById(formId);
          
          if (!form) {
            console.error('Form not found with ID:', formId);
            return;
          }
          
          console.log('Confirm button clicked, form found:', form);
          openModal({
            title: btn.getAttribute('data-title'),
            message: btn.getAttribute('data-message'),
            formId: formId
          });
        });

        // Close handlers
        overlay.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        // Confirm action
        okBtn.addEventListener('click', () => {
          console.log('Confirm button clicked, target form:', targetFormId);
          
          if (targetFormId) {
            const form = document.getElementById(targetFormId);
            if (form) {
              console.log('Submitting form:', form);
              form.submit();
            } else {
              console.error('Form not found with ID:', targetFormId);
            }
          }
          closeModal();
        });

        // ESC key close
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
          }
        });
        
        console.log('Confirm modal initialized successfully');
      })();
    } else {
      console.log('Browse page modals already initialized');
    }
  </script>
</x-userlayout>