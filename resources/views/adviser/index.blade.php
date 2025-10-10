<x-userlayout>
     
    <div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">Adviser Dashboard</h2>
    </div>
   
  </div>
</div>

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

          <li class="shadow rounded-lg p-3">
            <div class="font-medium">{{ $r->title->title }}</div>
            <div class="text-sm text-gray-500">Owner: {{ $r->title->owner->name }}</div>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
              @if($isAlreadyAssigned)
                <span class="px-2 py-1 rounded bg-gray-100 text-gray-700">
                  Assigned to: {{ optional($r->title->primaryAdviser ?? null)->name ?? 'another adviser' }}
                </span>
              @endif
            </div>

            <div class="mt-2 flex gap-2">
              <button
                type="button"
                class="px-3 py-1 bg-green-600 text-white rounded js-open-accept"
                data-action="{{ route('adviser.requests.accept', $r) }}"
                data-title="{{ $r->title->title }}"
                data-student="{{ $r->title->owner->name }}"
                @if($isAlreadyAssigned) disabled title="Title already assigned" class="px-3 py-1 rounded bg-gray-300 text-gray-600 cursor-not-allowed" @endif
              >
                Accept
              </button>

              <button
                type="button"
                class="px-3 py-1 bg-red-600 text-white rounded js-open-decline"
                data-action="{{ route('adviser.requests.decline', $r) }}"
                data-title="{{ $r->title->title }}"
                data-student="{{ $r->title->owner->name }}"
                @if($isAlreadyAssigned) disabled title="Title already assigned" class="px-3 py-1 rounded bg-gray-300 text-gray-600 cursor-not-allowed" @endif
              >
                Decline
              </button>
            </div>
          </li>

                @empty
                    <li class="text-gray-500">No pending requests.</li>
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
@endphp

@if(!$isAdviserRequest)
  @continue
@endif

<li class="shadow rounded-lg p-3">
  <div class="font-medium">{{ $r->title->title }}</div>
  <div class="text-sm text-gray-500">Owner: {{ $r->title->owner->name }}</div>

  <div class="mt-2 flex flex-wrap gap-2 text-xs">
    <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800">Request sent</span>
    @if($isAlreadyAssigned)
      <span class="px-2 py-1 rounded bg-gray-100 text-gray-700">
        Assigned to: {{ optional($r->title->primaryAdviser ?? null)->name ?? 'another adviser' }}
      </span>
    @endif
  </div>
</li>

                @empty
                    <li class="text-gray-500">You have not requested any titles yet.</li>
                @endforelse
            </ul>
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
        placeholder="Briefly explain why you’re declining (required)"
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
      <!-- Filled by JS, e.g., Accepting “Title” (Owner: Student) -->
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



<script>
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
    ctx.textContent = `Accepting “${title}” (Owner: ${student})`;
    ensureToBody(modal);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    ta.focus();
  }

  function closeAccept() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  // Open via any .js-open-accept button
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

  // Cancel, overlay, ESC close
  cancel && cancel.addEventListener('click', closeAccept);
  overlay && overlay.addEventListener('click', closeAccept);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAccept(); });

  // Submit: nothing to validate (note is optional)
  // The <textarea name="note"> posts directly to the controller.
})();
</script>


<script>
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
    ctx.textContent = `Declining “${title}” (Owner: ${student})`;
    ensureToBody(modal);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    ta.focus();
  }

  function closeDecline() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  // Open via any .js-open-decline button
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

  // Cancel, overlay, ESC close
  cancel && cancel.addEventListener('click', closeDecline);
  overlay && overlay.addEventListener('click', closeDecline);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDecline(); });

  // On submit, ensure reason is present → copy to hidden input
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
