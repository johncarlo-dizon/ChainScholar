<x-userlayout>
 

  <x-header.bar
  title="Approval Requests"
  subtitle="Research titles pending your review and approval"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

    <div class="container mx-auto px-4 py-6 space-y-4">
        @forelse($requests as $r)
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg">{{ $r->title->title }}</h3>
                        <div class="text-sm text-gray-500">Owner: {{ $r->title->owner->name }}</div>
                        <div class="text-xs text-gray-500 mt-1">Requested by: {{ ucfirst($r->requested_by) }}</div>
                        
                        {{-- Description section with modal trigger --}}
                        @php
                          $description = trim((string)($r->title->description ?? ''));
                        @endphp
                        @if($description !== '')
                          <div class="mt-4">
                            <div class="text-sm text-gray-700">
                              <div class="line-clamp-2">{{ Str::limit($description, 160, '…') }}</div>
                              <button 
                                type="button" 
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium mt-1 js-view-description"
                                data-title="{{ $r->title->title }}"
                                data-student="{{ $r->title->owner->name }}"
                                data-description="{{ $description }}"
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
                    <div class="flex gap-2 ml-4">
                        <button
                            type="button"
                            class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 js-open-accept"
                            data-action="{{ route('adviser.requests.accept', $r) }}"
                            data-title="{{ $r->title->title }}"
                            data-student="{{ $r->title->owner->name }}"
                        >
                            Accept
                        </button>

                       <button
                            type="button"
                            class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 js-open-decline"
                            data-action="{{ route('adviser.requests.decline', $r) }}"
                            data-title="{{ $r->title->title }}"
                            data-student="{{ $r->title->owner->name }}"
                        >
                            Decline
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-gray-500">No pending requests at the moment.</div>
        @endforelse

        <div>{{ $requests->links() }}</div>
    </div>

    <!-- Description View Modal -->
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

    <!-- Decline Modal -->
    <div id="decline-modal" class="fixed inset-0 hidden items-center justify-center z-50">
      <div id="decline-overlay" class="absolute inset-0 bg-black/30"></div>
      <div class="relative bg-white rounded-xl shadow-lg p-6 max-w-lg w-full mx-4 z-10">
        <h3 class="text-lg font-semibold text-gray-900">Confirm Decline</h3>
        <p id="decline-context" class="text-sm text-gray-600 mt-1">
          <!-- Filled by JS: e.g., Declining "Title" (Owner: Student) -->
        </p>

        <form id="decline-form" method="POST" class="mt-4 space-y-3">
          @csrf
          <!-- Keep POST, controller will handle status change -->
          <input type="hidden" name="reason" id="decline-reason-hidden">
          <label for="decline-reason" class="block text-sm font-medium text-gray-700">
            Reason for declining <span class="text-red-600">*</span>
          </label>
          <textarea
            id="decline-reason"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
            rows="2" maxlength="35"
            placeholder="Briefly explain why you're declining (required)"
            required
          ></textarea>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" id="decline-cancel"
              class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">
              Confirm Decline
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Accept Modal -->
    <div id="accept-modal" class="fixed inset-0 hidden items-center justify-center z-50">
      <div id="accept-overlay" class="absolute inset-0 bg-black/30"></div>
      <div class="relative bg-white rounded-xl shadow-lg p-6 max-w-lg w-full mx-4 z-10">
        <h3 class="text-lg font-semibold text-gray-900">Confirm Accept</h3>
        <p id="accept-context" class="text-sm text-gray-600 mt-1">
          <!-- Filled by JS, e.g., Accepting "Title" (Owner: Student) -->
        </p>

        <form id="accept-form" method="POST" class="mt-4 space-y-3">
          @csrf
          <!-- Optional welcome/note to student -->
          <label for="accept-note" class="block text-sm font-medium text-gray-700">
            Message to student (optional)
          </label>
          <textarea
            id="accept-note"
            name="note"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
            rows="2" maxlength="35"
            placeholder="Example: Looking forward to working with you."
          ></textarea>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" id="accept-cancel"
              class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">
              Confirm Accept
            </button>
          </div>
        </form>
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

    // Existing modal scripts (accept/decline) remain the same...
    (function () {
      const modal   = document.getElementById('accept-modal');
      const overlay = document.getElementById('accept-overlay');
      const cancel  = document.getElementById('accept-cancel');
      const form    = document.getElementById('accept-form');
      const ta      = document.getElementById('accept-note');
      const ctx     = document.getElementById('accept-context');

      function ensureToBody(el) {
        if (el && el.parentElement !== document.body) document.body.appendChild(el);
      }

      function openAccept(action, title, student) {
        form.action = action;
        ta.value = '';
        ctx.textContent = `Accepting "${title}" (Owner: ${student})`;
        ensureToBody(modal);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        ta.focus();
      }

      function closeAccept() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }

      document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-open-accept');
        if (!btn) return;
        e.preventDefault();
        const action  = btn.dataset.action;
        const title   = btn.dataset.title || 'Untitled';
        const student = btn.dataset.student || 'Student';
        if (!action) return;
        openAccept(action, title, student);
      });

      cancel && cancel.addEventListener('click', closeAccept);
      overlay && overlay.addEventListener('click', closeAccept);
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAccept(); });
    })();

    (function () {
      const modal   = document.getElementById('decline-modal');
      const overlay = document.getElementById('decline-overlay');
      const cancel  = document.getElementById('decline-cancel');
      const form    = document.getElementById('decline-form');
      const ta      = document.getElementById('decline-reason');
      const hidden  = document.getElementById('decline-reason-hidden');
      const ctx     = document.getElementById('decline-context');

      let currentAction = null;

      function ensureToBody(el) {
        if (el && el.parentElement !== document.body) document.body.appendChild(el);
      }

      function openDecline(action, title, student) {
        currentAction = action;
        form.action = action;
        hidden.value = '';
        ta.value = '';
        ctx.textContent = `Declining "${title}" (Owner: ${student})`;
        ensureToBody(modal);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        ta.focus();
      }

      function closeDecline() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }

      document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-open-decline');
        if (!btn) return;
        e.preventDefault();
        const action  = btn.dataset.action;
        const title   = btn.dataset.title || 'Untitled';
        const student = btn.dataset.student || 'Student';
        if (!action) return;
        openDecline(action, title, student);
      });

      cancel && cancel.addEventListener('click', closeDecline);
      overlay && overlay.addEventListener('click', closeDecline);
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDecline(); });

      form && form.addEventListener('submit', function (e) {
        const val = (ta.value || '').trim();
        if (!val) {
          e.preventDefault();
          ta.focus();
          ta.classList.add('ring-2','ring-red-500');
          setTimeout(() => ta.classList.remove('ring-2','ring-red-500'), 1200);
          return;
        }
        hidden.value = val;
      });
    })();
    </script>
</x-userlayout>