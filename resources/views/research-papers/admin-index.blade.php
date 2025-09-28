<x-userlayout>
    <div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 p-6 shadow mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-semibold text-white">All Research Papers</h2>
                <p class="text-white/90 mt-1 text-sm">Manage all uploaded research papers</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <!-- Search and Filters -->
        <div class="mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Search papers...">
                </div>

                <!-- User Filter -->
                <div>
                    <label for="user" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select name="user" id="user"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department" id="department"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Program Filter -->
                <div>
                    <label for="program" class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <select name="program" id="program"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                {{ $program }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <select name="year" id="year"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="md:col-span-5 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('research-papers.admin-index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Papers Table -->
     <!-- Papers Table -->
<div class="overflow-x-auto border border-gray-200 rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
       <thead class="bg-gray-50">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Authors</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yr</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
    </tr>
    </thead>

        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($papers as $paper)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ Str::limit($paper->title, 50) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ Str::limit($paper->authors, 30) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"> 
                                       {{ Str::limit($paper->program, 30) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $paper->year }}
                    </td>
           
          <td class="px-6 py-4 whitespace-nowrap">
@php
  $status = in_array($paper->chain_status, ['REGISTERED','CONFIRMED'], true)
              ? 'REGISTERED'
              : ($paper->chain_status ?? 'NONE');

  $cls = match ($status) {
    'REGISTERED' => 'bg-amber-50 text-amber-700',
    'UPLOADED'   => 'bg-sky-50 text-sky-700',
    default      => 'bg-gray-100 text-gray-700',
  };
@endphp
<span class="inline-flex items-center rounded px-2 py-1 text-xs font-medium {{ $cls }}">
  {{ $status }}
</span>

  @php
    $tx = $paper->tx_hash;
    $explorer = match ((int)($paper->chain_id ?? 0)) {
      80002     => 'https://amoy.polygonscan.com/tx/',
      11155111  => 'https://sepolia.etherscan.io/tx/',
      default   => null,
    };
  @endphp
  @if($tx && $explorer)
    <a href="{{ $explorer.$tx }}" target="_blank" class="ml-2 text-xs text-indigo-600 hover:underline">View tx</a>
  @endif

  {{-- Request pill --}}
  @if($paper->pendingRequest)
    <span class="ml-2 inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-yellow-50 text-yellow-700">
      Request: PENDING
    </span>
  @endif
</td>


<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3 flex items-center">
  {{-- Details --}}
<button type="button" class="text-sky-600 hover:text-sky-800 btn-details" title="Details"
    data-title="{{ e($paper->title) }}"
    data-authors="{{ e($paper->authors) }}"
    data-department="{{ e($paper->department) }}"
    data-program="{{ e($paper->program) }}"
    data-year="{{ e($paper->year) }}"
    data-user-name="{{ e(optional($paper->user)->name) }}"
    data-user-email="{{ e(optional($paper->user)->email) }}"
    data-uploaded="{{ $paper->created_at->format('M d, Y') }}"
    data-file-url="{{ Storage::url($paper->file_path) }}"
    data-abstract="{{ e($paper->abstract ?? '') }}"
    data-plagiarism="{{ is_null($paper->plagiarism_score) ? '' : (int)$paper->plagiarism_score }}">
    <i data-feather="info" class="w-5 h-5"></i>
</button>


  {{-- Admin-only actions --}}
  @if(auth()->user()->isAdmin())
    {{-- Hash (only if not hashed) --}}
    @if (!$paper->sha256)
      <form method="POST" action="{{ route('papers.hash', $paper) }}" class="inline">
        @csrf
        <button class="text-gray-700 hover:text-gray-900" title="Compute SHA-256">
          <i data-feather="hash" class="w-5 h-5"></i>
        </button>
      </form>
    @endif

    {{-- Approve & Register (if pending request) --}}
    @if ($paper->pendingRequest && $paper->sha256 && $paper->chain_status!=='REGISTERED' && $paper->chain_status!=='CONFIRMED')
      <button type="button"
        class="text-emerald-600 hover:text-emerald-800 btn-register-chain"
        title="Approve & Register"
        data-request-id="{{ $paper->pendingRequest->id }}"
        data-approve="{{ route('requests.approve', $paper->pendingRequest->id) }}"
        data-action="{{ route('papers.register', $paper) }}"
        data-confirm="{{ route('papers.confirm', $paper) }}"
        data-sha="{{ $paper->sha256 }}"
        data-title="{{ e(Str::limit($paper->title, 80)) }}">
        <i data-feather="check-circle" class="w-5 h-5"></i>
      </button>

      {{-- Decline with reason --}}
      <button type="button"
        class="text-rose-600 hover:text-rose-800 btn-decline-request"
        data-decline="{{ route('requests.decline', $paper->pendingRequest->id) }}"
        data-paper-title="{{ e(Str::limit($paper->title, 80)) }}">
        <i data-feather="x-circle" class="w-5 h-5"></i>
      </button>
    @endif

    {{-- Plain Register (no request needed) --}}
    @if (!$paper->pendingRequest && $paper->sha256 && $paper->chain_status!=='REGISTERED' && $paper->chain_status!=='CONFIRMED')
      <button type="button"
        class="text-emerald-600 hover:text-emerald-800 btn-register-chain"
        title="Register on-chain"
        data-action="{{ route('papers.register', $paper) }}"
        data-confirm="{{ route('papers.confirm', $paper) }}"
        data-sha="{{ $paper->sha256 }}"
        data-title="{{ e(Str::limit($paper->title, 80)) }}">
        <i data-feather="link-2" class="w-5 h-5"></i>
      </button>
    @endif
  @endif

  {{-- Verify (read-only) --}}
  <button type="button" class="text-gray-700 hover:text-gray-900 btn-verify-chain"
    title="Verify on-chain"
    data-sha="{{ $paper->sha256 }}"
    data-title="{{ e(Str::limit($paper->title, 80)) }}">
    <i data-feather="shield-check" class="w-5 h-5"></i>
  </button>

  {{-- Delete --}}
  <button type="button" class="text-red-600 hover:text-red-900 btn-delete"
    title="Delete"
    data-action="{{ route('research-papers.destroy', $paper->id) }}"
    data-title="{{ e(Str::limit($paper->title, 80)) }}">
    <i data-feather="trash-2" class="w-5 h-5"></i>
  </button>
</td>



                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                        No research papers found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


        <!-- Pagination -->
        <div class="mt-6">
            {{ $papers->links() }}
        </div>
    </div>

 

<!-- Decline Request Modal -->
<div id="declineModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 backdrop-blur-sm bg-black/10" data-close-decline></div>
  <div class="relative mx-auto my-8 mt-20 w-full max-w-lg bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
      <h3 class="text-lg font-semibold text-gray-900">Decline Blockchain Request</h3>
      <button type="button" class="text-gray-500 hover:text-gray-700" data-close-decline>&times;</button>
    </div>
    <form id="declineForm" method="POST">
      @csrf
      <div class="mt-4 space-y-2 text-sm">
        <p class="text-gray-500">Paper</p>
        <p id="declinePaper" class="text-gray-900 font-medium"></p>
        <label class="block text-gray-700 mt-3">Reason</label>
        <textarea name="reason" required class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="4" placeholder="Explain why this request is declined"></textarea>
      </div>
      <div class="mt-6 flex items-center justify-end gap-3">
        <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-decline>Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-white hover:bg-rose-700">Decline</button>
      </div>
    </form>
  </div>
</div>



 <!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <!-- Blur overlay -->
    <div class="absolute inset-0 backdrop-blur-sm bg-transparent" data-close-delete></div>

    <!-- Modal card -->
    <div class="relative mx-auto my-8 w-full max-w-lg bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>

        </div>

        <div class="mt-4 space-y-3">
            <p class="text-sm text-gray-600">You’re about to delete:</p>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p id="del-title" class="text-sm font-medium text-gray-900"></p>
            </div>
            <div class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
                This action is irreversible. The file and its metadata will be permanently removed.
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-delete>
                Cancel
            </button>

            <!-- Hidden form submitted by JS -->
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button id="confirmDeleteBtn" type="submit"
                        class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700 disabled:opacity-50">
                    <svg id="confirmDeleteSpinner" class="mr-2 hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                        <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>


    <!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 backdrop-blur-sm bg-transparent" data-close-modal></div>

    <div class="relative mx-auto my-8 w-full max-w-2xl bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Research Paper Details</h3>
        </div>

        <div class="mt-4 space-y-4 text-sm">
            <div>
                <p class="text-gray-500">Title</p>
                <p id="m-title" class="font-medium text-gray-900"></p>
            </div>
        <div class="grid sm:grid-cols-2 gap-4">
    <div>
        <p class="text-gray-500">Authors</p>
        <p id="m-authors" class="text-gray-900"></p>
    </div>
    <div>
        <p class="text-gray-500">Department</p>
        <p id="m-department" class="text-gray-900"></p>
    </div>
    <div>
        <p class="text-gray-500">Program</p>
        <p id="m-program" class="text-gray-900"></p>
    </div>
    <div>
        <p class="text-gray-500">Year</p>
        <p id="m-year" class="text-gray-900"></p>
    </div>
    <div>
        <p class="text-gray-500">Uploaded</p>
        <p id="m-uploaded" class="text-gray-900"></p>
    </div>
    <div>
        <p class="text-gray-500">User</p>
        <p id="m-user" class="text-gray-900"></p>
    </div>

    <!-- NEW: Plagiarism -->
    <div class="sm:col-span-2">
        <p class="text-gray-500">Plagiarism</p>
        <span id="m-plag"
              class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700">—</span>
        <span class="ml-2 text-xs text-gray-500">(Saved score)</span>
    </div>
</div>


            <div id="m-abstract-wrap" class="hidden">
                <p class="text-gray-500">Abstract</p>
                <p id="m-abstract" class="text-gray-900 whitespace-pre-line"></p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a id="m-file-url" href="#" target="_blank"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                Open PDF
            </a>
            <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-modal>Close</button>
        </div>
    </div>
</div>


{{-- Verify Modal --}}
<div id="verifyModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 backdrop-blur-sm bg-black/10" data-close-verify></div>
  <div class="relative mx-auto my-8  mt-20 w-full max-w-lg bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
      <h3 class="text-lg font-semibold text-gray-900">Verify Registration</h3>
      <button type="button" class="text-gray-500 hover:text-gray-700" data-close-verify>&times;</button>
    </div>
    <div class="mt-4 space-y-3">
      <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
        <p class="text-xs text-gray-500">Paper</p>
        <p id="ver-paper-title" class="text-sm font-medium text-gray-900"></p>
      </div>
      <div class="text-sm">
        <p class="text-gray-500">Digest (bytes32)</p>
        <p id="ver-digest" class="font-mono text-xs break-all"></p>
      </div>
      <div id="ver-result" class="rounded-md px-3 py-2 text-sm hidden"></div>
    </div>
    <div class="mt-6 flex items-center justify-end gap-3">
      <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-verify>Close</button>
    </div>
  </div>
</div>


<!-- Approve & Register Modal -->
<div id="approveModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 backdrop-blur-sm bg-black/10" data-close-approve></div>
  <div class="relative mx-auto  mt-20 my-8 w-full max-w-lg bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
      <h3 id="approveHeading" class="text-lg font-semibold text-gray-900">Approve & Register</h3>
      <button type="button" class="text-gray-500 hover:text-gray-700" data-close-approve>&times;</button>
    </div>

    <div class="mt-4 space-y-3 text-sm">
      <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
        <p class="text-xs text-gray-500">Paper</p>
        <p id="appr-paper-title" class="text-sm font-medium text-gray-900"></p>
      </div>

      <div class="text-sm">
        <p class="text-gray-500">Digest (bytes32)</p>
        <p id="appr-digest" class="font-mono text-xs break-all"></p>
      </div>

      <div id="approveNote" class="rounded-md bg-amber-50 px-3 py-2 text-amber-700">
        This will <strong>approve</strong> the pending request and send a transaction via MetaMask on Polygon Amoy.
      </div>
    </div>

    <div class="mt-6 flex items-center justify-end gap-3">
      <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-approve>Cancel</button>
      <button id="approveConfirmBtn" type="button"
              class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700"
              data-action="" data-confirm="" data-approve="" data-sha="" data-request-id="" data-title="">
        <svg id="approveSpinner" class="mr-2 hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
          <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
        </svg>
        Confirm
      </button>
    </div>
  </div>
</div>




<script>
(function () {
    const modal = document.getElementById('detailsModal');
    const closeEls = modal.querySelectorAll('[data-close-modal]');

    const setText = (selector, value, fallback = '—') => {
        const el = document.querySelector(selector);
        if (!el) return;
        el.textContent = (value && String(value).trim() !== '') ? value : fallback;
    };

    const openModalFromBtn = (btn) => {
        setText('#m-title', btn.dataset.title);
        setText('#m-authors', btn.dataset.authors);
        setText('#m-department', btn.dataset.department);
        setText('#m-program', btn.dataset.program);
        setText('#m-year', btn.dataset.year);
        setText('#m-uploaded', btn.dataset.uploaded);
        setText('#m-user', [btn.dataset.userName, btn.dataset.userEmail].filter(Boolean).join(' ') || '—');
        // Plagiarism badge (uses saved score)
const plagEl = document.getElementById('m-plag');
let cls = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ';
let txt = '—';

const raw = btn.dataset.plagiarism;
if (raw !== undefined && raw !== '' && !Number.isNaN(Number(raw))) {
    const val = Number(raw);
    txt = `${val}%`;
    // Threshold same as upload gate (45)
    cls += (val >= 45)
        ? 'bg-red-100 text-red-700'
        : 'bg-green-100 text-green-700';
} else {
    cls += 'bg-gray-100 text-gray-700';
}

plagEl.textContent = txt;
plagEl.className = cls;


        const abstract = btn.dataset.abstract || '';
        const abstractWrap = document.getElementById('m-abstract-wrap');
        const abstractEl = document.getElementById('m-abstract');
        if (abstract.trim()) {
            abstractEl.textContent = abstract;
            abstractWrap.classList.remove('hidden');
        } else {
            abstractEl.textContent = '';
            abstractWrap.classList.add('hidden');
        }

        const fileUrl = btn.dataset.fileUrl || '#';
        const link = document.getElementById('m-file-url');
        link.setAttribute('href', fileUrl);

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // Delegate clicks for any .btn-details (works with pagination too)
    document.addEventListener('click', (e) => {
        const detailsBtn = e.target.closest('.btn-details');
        if (detailsBtn) {
            e.preventDefault();
            openModalFromBtn(detailsBtn);
        }

        if (e.target.hasAttribute('data-close-modal') || e.target === modal.querySelector('.absolute.inset-0')) {
            closeModal();
        }
    });

    // ESC to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    // Also close when clicking the dark overlay
    modal.querySelector('.absolute.inset-0').addEventListener('click', closeModal);
})();
</script>



<script>
(function () {
    // DELETE MODAL WIRING
    const deleteModal = document.getElementById('deleteModal');
    const deleteForm  = document.getElementById('deleteForm');
    const delTitleEl  = document.getElementById('del-title');
    const confirmBtn  = document.getElementById('confirmDeleteBtn');
    const spinnerEl   = document.getElementById('confirmDeleteSpinner');

    const openDelete = (action, title) => {
        deleteForm.setAttribute('action', action);
        delTitleEl.textContent = title || 'This research paper';
        deleteModal.classList.remove('hidden');
        document.documentElement.classList.add('overflow-hidden'); // lock scroll
    };

    const closeDelete = () => {
        deleteModal.classList.add('hidden');
        document.documentElement.classList.remove('overflow-hidden');
        confirmBtn.disabled = false;
        spinnerEl.classList.add('hidden');
    };

    // Delegate clicks for delete buttons (works across pagination)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-delete');
        if (btn) {
            e.preventDefault();
            openDelete(btn.dataset.action, btn.dataset.title);
        }
        if (e.target.hasAttribute('data-close-delete')) {
            closeDelete();
        }
    });

    // ESC closes modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
            closeDelete();
        }
    });

    // Click overlay to close
    deleteModal.querySelector('[data-close-delete]').addEventListener('click', closeDelete);

    // Submit UX
    deleteForm.addEventListener('submit', () => {
        confirmBtn.disabled = true;
        spinnerEl.classList.remove('hidden');
    });
})();
</script>

{{-- Feather icons --}}
<script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();</script>
{{-- Ethers.js v6 (UMD) --}}
<script src="https://cdn.jsdelivr.net/npm/ethers@6.12.1/dist/ethers.umd.min.js"></script>

<script>
(() => {
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

  // Chain/Contract (Polygon Amoy by default)
  const CHAIN_ID_DEC = 80002;
  const CHAIN_ID_HEX = '0x13882';
  const RPC_URL      = 'https://rpc-amoy.polygon.technology';
  const CONTRACT_ADDRESS = @json(config('chain.amoy_contract', env('CONTRACT_80002', '0xYourContractHere')));
  const CONTRACT_ABI = [
    "function register(bytes32 digest) external",
    "function getTimestamp(bytes32 digest) view returns (uint256)",
    "event Registered(bytes32 indexed digest, address indexed sender, uint256 blockTime)"
  ];

  const toBytes32Digest = (sha256Hex) => {
    if (!sha256Hex) throw new Error('Missing sha256');
    const h = String(sha256Hex).toLowerCase().replace(/^0x/, '');
    if (h.length !== 64) throw new Error('sha256 must be 64 hex chars');
    return '0x' + h;
  };

  const waitForEthereum = () =>
    new Promise((resolve) => {
      if (window.ethereum) return resolve(window.ethereum);
      window.addEventListener('ethereum#initialized', () => resolve(window.ethereum), { once:true });
      setTimeout(() => resolve(window.ethereum), 1500);
    });

  async function getMetaMaskProvider() {
    await waitForEthereum();
    if (window.ethereum?.isMetaMask) return window.ethereum;
    const list = window.ethereum?.providers;
    if (Array.isArray(list)) {
      const mm = list.find(p => p?.isMetaMask);
      if (mm) return mm;
    }
    return window.ethereum || null;
  }

  const ensureAmoy = async (eth) => {
    const current = await eth.request({ method:'eth_chainId' });
    if (current === CHAIN_ID_HEX) return;
    try {
      await eth.request({ method:'wallet_switchEthereumChain', params:[{ chainId: CHAIN_ID_HEX }] });
    } catch (err) {
      if (err?.code === 4902) {
        await eth.request({
          method: 'wallet_addEthereumChain',
          params: [{
            chainId: CHAIN_ID_HEX,
            chainName: 'Polygon Amoy',
            nativeCurrency: { name: 'MATIC', symbol: 'MATIC', decimals: 18 },
            rpcUrls: [RPC_URL],
            blockExplorerUrls: ['https://amoy.polygonscan.com/']
          }]
        });
      } else { throw err; }
    }
  };

  const saveRegistrationToServer = ({actionUrl, wallet, txHash, chainId}) =>
    fetch(actionUrl, {
      method: 'POST',
      headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json, text/html'},
      body: JSON.stringify({ wallet, tx_hash: txHash, chain_id: chainId })
    });

  const confirmOnServer = (confirmUrl) =>
    fetch(confirmUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });

  // REGISTER (admin triggers user’s paper on-chain) + optional approve-first
// APPROVE & REGISTER — Modal driven
let MM_LOCK = false;

const approveModal      = document.getElementById('approveModal');
const approveHeading    = document.getElementById('approveHeading');
const approveNote       = document.getElementById('approveNote');
const approveConfirmBtn = document.getElementById('approveConfirmBtn');
const approveSpinner    = document.getElementById('approveSpinner');
const apprTitleEl       = document.getElementById('appr-paper-title');
const apprDigestEl      = document.getElementById('appr-digest');

const openApprove = (btn) => {
  const hasRequest = !!btn.dataset.requestId;
  approveHeading.textContent = hasRequest ? 'Approve & Register' : 'Register on-chain';
  approveNote.innerHTML = hasRequest
    ? 'This will <strong>approve</strong> the pending request and send a transaction via MetaMask on Polygon Amoy.'
    : 'This will send a transaction via MetaMask on Polygon Amoy.';

  const title  = btn.dataset.title || 'Paper';
  const digest = toBytes32Digest(btn.dataset.sha); // will throw if invalid

  apprTitleEl.textContent  = title;
  apprDigestEl.textContent = digest;

  // stash all needed data on the confirm button
  approveConfirmBtn.dataset.action     = btn.dataset.action || '';
  approveConfirmBtn.dataset.confirm    = btn.dataset.confirm || '';
  approveConfirmBtn.dataset.approve    = btn.dataset.approve || '';
  approveConfirmBtn.dataset.sha        = btn.dataset.sha || '';
  approveConfirmBtn.dataset.requestId  = btn.dataset.requestId || '';
  approveConfirmBtn.dataset.title      = title;

  approveModal.classList.remove('hidden');
  document.documentElement.classList.add('overflow-hidden');
};

const closeApprove = () => {
  approveModal.classList.add('hidden');
  document.documentElement.classList.remove('overflow-hidden');
};

document.querySelectorAll('.btn-register-chain').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    openApprove(btn);
  }, { passive: true });
});

approveModal.querySelectorAll('[data-close-approve]').forEach(el => el.addEventListener('click', closeApprove));
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && !approveModal.classList.contains('hidden')) closeApprove();
});

// Modal "Confirm" → run approve (if any) + MetaMask register + server confirm
approveConfirmBtn.addEventListener('click', async () => {
  if (MM_LOCK || approveConfirmBtn.dataset.busy === '1') return;
  MM_LOCK = true; approveConfirmBtn.dataset.busy = '1';
  approveConfirmBtn.disabled = true; approveSpinner.classList.remove('hidden');

  try {
    const digest    = toBytes32Digest(approveConfirmBtn.dataset.sha);
    const actionUrl = approveConfirmBtn.dataset.action;
    const confirmUrl= approveConfirmBtn.dataset.confirm;
    const approveUrl= approveConfirmBtn.dataset.approve;
    const hasRequest= !!approveConfirmBtn.dataset.requestId;

    // Close modal so MetaMask can pop up nicer
    closeApprove();

    // 1) If this was triggered from a pending request, approve it server-side first
    if (hasRequest && approveUrl) {
      await fetch(approveUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    }

    // 2) MetaMask flow
    const eth = await getMetaMaskProvider();
    if (!eth) throw new Error('MetaMask provider not found. Enable the extension.');

    const accounts = await eth.request({ method: 'eth_requestAccounts' });
    const account  = accounts?.[0];
    if (!account) throw new Error('No account selected in MetaMask.');

    await ensureAmoy(eth);

    const provider = new ethers.BrowserProvider(eth);
    const signer   = await provider.getSigner();
    const contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer);

    // 3) Send tx
    const tx = await contract.register(digest);

    // 4) Save REGISTERED on server (also notifies)
    await saveRegistrationToServer({
      actionUrl,
      wallet: account,
      txHash: tx.hash,
      chainId: CHAIN_ID_DEC
    });

    // 5) Wait 1 conf -> confirm on server -> reload
    const receipt = await tx.wait(1);
    if (receipt?.status === 1) {
      await confirmOnServer(confirmUrl);
      location.reload();
    } else {
      alert('Transaction failed or reverted.');
    }
  } catch (e) {
    const msg = e?.message || String(e);
    if (e?.code === -32002 || msg.includes('already pending')) {
      alert('A MetaMask request is already open. Finish/close it, then try again.');
    } else {
      alert(msg);
    }
  } finally {
    MM_LOCK = false; approveConfirmBtn.dataset.busy = '0';
    approveConfirmBtn.disabled = false; approveSpinner.classList.add('hidden');
  }
}, { passive: true });


  // DECLINE modal wiring
  (() => {
    const modal = document.getElementById('declineModal');
    const form  = document.getElementById('declineForm');
    const paperEl = document.getElementById('declinePaper');

    const open = (declineUrl, paperTitle) => {
      form.setAttribute('action', declineUrl);
      paperEl.textContent = paperTitle || 'This paper';
      modal.classList.remove('hidden');
      document.documentElement.classList.add('overflow-hidden');
    };
    const close = () => {
      modal.classList.add('hidden');
      document.documentElement.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('.btn-decline-request').forEach(btn => {
      btn.addEventListener('click', () => open(btn.dataset.decline, btn.dataset.paperTitle));
    });
    modal.querySelectorAll('[data-close-decline]').forEach(el => el.addEventListener('click', close));
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });
  })();

  // VERIFY (read-only)
  (function(){
    const verifyModal = document.getElementById('verifyModal');
    const verTitleEl  = document.getElementById('ver-paper-title');
    const verDigestEl = document.getElementById('ver-digest');
    const verResult   = document.getElementById('ver-result');

    const openVerify = () => { verifyModal.classList.remove('hidden'); document.documentElement.classList.add('overflow-hidden'); };
    const closeVerify = () => { verifyModal.classList.add('hidden'); document.documentElement.classList.remove('overflow-hidden'); };
    verifyModal?.querySelectorAll('[data-close-verify]').forEach(el => el.addEventListener('click', closeVerify));
    document.addEventListener('keydown', (e) => { if (!verifyModal.classList.contains('hidden') && e.key === 'Escape') closeVerify(); });

    document.querySelectorAll('.btn-verify-chain').forEach(btn => {
      btn.addEventListener('click', async () => {
        try {
          const digest = toBytes32Digest(btn.dataset.sha);
          const title  = btn.dataset.title || 'Paper';

          verTitleEl.textContent = title;
          verDigestEl.textContent = digest;
          verResult.classList.add('hidden');

          const ro = new ethers.JsonRpcProvider(RPC_URL);
          const contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, ro);
          const ts = await contract.getTimestamp(digest);

          verResult.classList.remove('hidden');
          const tsNum = typeof ts === 'bigint' ? Number(ts) : Number(ts);
          if (tsNum > 0) {
            const d = new Date(tsNum * 1000);
            verResult.className = "rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-700";
            verResult.textContent = `✅ Registered on ${d.toLocaleString()}`;
          } else {
            verResult.className = "rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700";
            verResult.textContent = "❌ Not found on chain";
          }
          openVerify();
        } catch (e) {
          alert(e?.message || e);
        }
      }, { passive:true });
    });
  })();
})();
</script>


</x-userlayout>