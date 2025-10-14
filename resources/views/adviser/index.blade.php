<x-userlayout> 
     
<x-header.bar
  title="Dashboard"
  :subtitle="'Welcome back, ' . (Auth::user()->name ?? 'User') . '!'"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

    <div class="container mx-auto px-4 py-6 grid md:grid-cols-3 gap-4">
        <a href="{{ route('adviser.requests.pending') }}" class="block bg-white p-5 rounded-xl shadow hover:shadow-md">
            <div class="text-gray-500 text-sm">Pending Requests</div>
            <div class="text-3xl font-bold">{{ $pendingCount }}</div>
        </a>

      <a href="{{ route('adviser.advised.index') }}" class="block bg-white p-5 rounded-xl shadow hover:shadow-md">
            <div class="text-gray-500 text-sm">My Advised Titles</div>
            <div class="text-3xl font-bold">{{ $myAdvisedCount }}</div>
         </a>

        <a href="{{ route('adviser.titles.browse') }}" class="block bg-white p-5 rounded-xl shadow hover:shadow-md">
            <div class="text-gray-500 text-sm">Browse Open Titles</div>
            <div class="text-xl mt-1">Request to Advise →</div>
        </a>
    </div>

    <div class="container mx-auto px-4 pb-10 grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-semibold mb-3">Incoming Pending Requests</h3>
            <ul class="space-y-3">
                @forelse($incomingRequests as $r)
                    @php
  $isStudentRequest = ($r->requested_by === 'student') && ($r->status === 'pending') && is_null($r->decided_at);
  $isAlreadyAssigned = !is_null($r->title->primary_adviser_id);
@endphp

@if(!$isStudentRequest)
  @continue
@endif

          <li class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
            <div class="font-medium text-gray-900">{{ $r->title->title }}</div>
            <div class="text-sm text-gray-500 mt-1">Owner: {{ $r->title->owner->name }}</div>
            
            {{-- Description section with modal trigger --}}
            @php
              $desc = trim((string)($r->title->description ?? ''));
            @endphp
            @if($desc !== '')
              <div class="mt-3">
                <div class="text-sm text-gray-700">
                  <div class="line-clamp-2">{{ Str::limit($desc, 160, '…') }}</div>
                  <button 
                    type="button" 
                    class="text-blue-600 hover:text-blue-800 text-xs font-medium mt-1 js-view-description"
                    data-title="{{ $r->title->title }}"
                    data-student="{{ $r->title->owner->name }}"
                    data-description="{{ $desc }}"
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

            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
              @if($isAlreadyAssigned)
                <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 border">
                  Assigned to: {{ optional($r->title->primaryAdviser ?? null)->name ?? 'another adviser' }}
                </span>
              @endif
            </div>

            <div class="mt-4 flex gap-2">
              <button
                type="button"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors js-open-accept font-medium text-xs"
                data-action="{{ route('adviser.requests.accept', $r) }}"
                data-title="{{ $r->title->title }}"
                data-student="{{ $r->title->owner->name }}"
                @if($isAlreadyAssigned) disabled title="Title already assigned" class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 cursor-not-allowed font-medium text-xs" @endif
              >
                Accept
              </button>

              <button
                type="button"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors js-open-decline font-medium text-xs"
                data-action="{{ route('adviser.requests.decline', $r) }}"
                data-title="{{ $r->title->title }}"
                data-student="{{ $r->title->owner->name }}"
                @if($isAlreadyAssigned) disabled title="Title already assigned" class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 cursor-not-allowed font-medium text-xs" @endif
              >
                Decline
              </button>
            </div>
          </li>

                @empty
                    <li class="text-gray-500 text-center py-4 border border-gray-200 rounded-lg">No pending requests.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-semibold mb-3">My Pending Sent Requests</h3>
            <ul class="space-y-3">
                @forelse($myPendingSent as $r)
                   @php
  $isAdviserRequest = ($r->requested_by === 'adviser') && ($r->status === 'pending') && is_null($r->decided_at);
  $isAlreadyAssigned = !is_null($r->title->primary_adviser_id);
  $isAwaitingAdmin = $r->title->status === 'awaiting_admin';
@endphp

@if(!$isAdviserRequest)
  @continue
@endif

<li class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
  <div class="font-medium text-gray-900">{{ $r->title->title }}</div>
  <div class="text-sm text-gray-500 mt-1">Owner: {{ $r->title->owner->name }}</div>
  
  {{-- Description section with modal trigger --}}
  @php
    $desc = trim((string)($r->title->description ?? ''));
  @endphp
  @if($desc !== '')
    <div class="mt-3">
      <div class="text-sm text-gray-700">
        <div class="line-clamp-2">{{ Str::limit($desc, 160, '…') }}</div>
        <button 
          type="button" 
          class="text-blue-600 hover:text-blue-800 text-xs font-medium mt-1 js-view-description"
          data-title="{{ $r->title->title }}"
          data-student="{{ $r->title->owner->name }}"
          data-description="{{ $desc }}"
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

  {{-- Badges and Cancel Button in one row --}}
  <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
    <div class="flex flex-wrap gap-2 text-xs">
      <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 border border-yellow-200">Request sent</span>
      @if($isAlreadyAssigned)
        <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 border">
          Assigned to: {{ optional($r->title->primaryAdviser ?? null)->name ?? 'another adviser' }}
        </span>
      @endif
      @if($isAwaitingAdmin)
        <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 border border-blue-200">
          Awaiting admin approval
        </span>
      @endif
    </div>

    {{-- Cancel Request Button - smaller and aligned with badges --}}
    @if(!$isAlreadyAssigned && !$isAwaitingAdmin)
      <form method="POST" action="{{ route('adviser.requests.cancel', $r) }}" 
            id="cancelForm-{{ $r->id }}" class="flex-shrink-0">
        @csrf
        <button type="button"
                class="px-4 py-2  bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition-colors font-medium"
                data-confirm
                data-title="Cancel Request"
                data-message="Cancel your request to advise &quot;{{ $r->title->title }}&quot;?"
                data-form="cancelForm-{{ $r->id }}">
          Cancel Request
        </button>
      </form>
    @elseif($isAwaitingAdmin)
      <button class="px-3 py-1 bg-gray-300 text-gray-500 text-xs rounded-lg cursor-not-allowed font-medium" disabled>
        Cannot cancel
      </button>
    @else
      <button class="px-3 py-1 bg-gray-300 text-gray-500 text-xs rounded-lg cursor-not-allowed font-medium" disabled>
        Cannot cancel
      </button>
    @endif
  </div>
</li>

                @empty
                    <li class="text-gray-500 text-center py-4 border border-gray-200 rounded-lg">You have not requested any titles yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

<!-- Alternative: Entire modal with rounded corners -->
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

    <!-- Confirm Modal (for cancel requests) -->
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

    // Confirm Modal functionality for cancel requests
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
    </script>
</x-userlayout>