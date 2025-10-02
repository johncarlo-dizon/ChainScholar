<x-userlayout>
    {{-- Header --}}
    <div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 p-6 shadow mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-semibold text-white">My Research Papers</h2>
                <p class="text-white/90 mt-1 text-sm">View all your uploaded research papers</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Search papers...">
                </div>

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

                <div class="md:col-span-4 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('research-papers.student-index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-visible border border-gray-200 rounded-lg">

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Authors</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($papers as $paper)
                    @php
  // Treat CONFIRMED the same as REGISTERED for display/logic
  $displayStatus = in_array($paper->chain_status, ['REGISTERED','CONFIRMED'], true)
      ? 'REGISTERED'
      : ($paper->chain_status ?? 'NONE');

  $isRegistered = ($displayStatus === 'REGISTERED');

  $badgeClass = match ($displayStatus) {
    'REGISTERED' => 'bg-amber-50 text-amber-700',
    'UPLOADED'   => 'bg-sky-50 text-sky-700',
    default      => 'bg-gray-100 text-gray-700',
  };
@endphp

                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ Str::limit($paper->title, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ Str::limit($paper->authors, 30) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $paper->program }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $paper->year }}</td>

               <td class="px-6 py-4 whitespace-nowrap">
  <span class="inline-flex items-center rounded px-2 py-1 text-xs font-medium {{ $badgeClass }}">
    {{ $displayStatus }}
  </span>

  @php $lr = $paper->lastRequest; @endphp

  {{-- Request state for students/advisers --}}
  @if($paper->pendingRequest)
    <span class="ml-2 inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-yellow-50 text-yellow-700">
      Request: PENDING • {{ $paper->pendingRequest->created_at->diffForHumans() }}
    </span>
  @elseif($lr && $lr->status === 'REFUSED')
    <span
      class="ml-2 inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-rose-50 text-rose-700"
      title="{{ $lr->reason ? 'Reason: '.e($lr->reason) : 'No reason provided' }}">
      Request: DECLINED
    </span>
  @elseif($lr && $lr->status === 'APPROVED')
    <span class="ml-2 inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-emerald-50 text-emerald-700">
  
    </span>
  @endif

  @php
    $tx = $paper->tx_hash;
    $explorer = match ((int)($paper->chain_id ?? 0)) {
        80002 => 'https://amoy.polygonscan.com/tx/',
        11155111 => 'https://sepolia.etherscan.io/tx/',
        default => null,
    };
  @endphp
  @if($tx && $explorer)
    <a href="{{ $explorer.$tx }}" target="_blank" class="ml-2 text-xs text-indigo-600 hover:underline">View tx</a>
  @endif
</td>



                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium relative">
  {{-- 3-dot trigger --}}
  <button type="button"
          class="paper-actions-trigger inline-flex items-center justify-center w-9 h-9 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          aria-haspopup="menu"
          aria-expanded="false"
          data-menu-id="menu-{{ $paper->id }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
      <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
  </button>

  {{-- Dropdown (absolutely-positioned; won’t be clipped due to wrapper change) --}}
  <div id="menu-{{ $paper->id }}"
       class="paper-actions-menu hidden absolute right-0 top-full mt-2 z-50 min-w-[220px] rounded-lg border border-gray-300 bg-white p-1 shadow-lg  ">
    {{-- Details (opens modal) --}}
    <button type="button"
            class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-gray-700 text-left btn-details"
            title="Details"
            data-title="{{ e($paper->title) }}"
            data-authors="{{ e($paper->authors) }}"
            data-department="{{ e($paper->department) }}"
            data-program="{{ e($paper->program) }}"
            data-year="{{ e($paper->year) }}"
            data-uploaded="{{ $paper->created_at->format('M d, Y') }}"
            data-file-url="{{ Storage::url($paper->file_path) }}"
            data-abstract="{{ e($paper->abstract ?? '') }}"
            data-plagiarism="{{ is_null($paper->plagiarism_score) ? '' : (int)$paper->plagiarism_score }}">
      <i data-lucide="info" class="w-4 h-4"></i>
      <span>View details</span>
    </button>

    {{-- Certificate (if eligible) --}}
    @php
      $certEligible = in_array(($paper->chain_status ?? ''), ['REGISTERED','CONFIRMED'], true) && !empty($paper->tx_hash) && !empty($paper->sha256);
    @endphp
    @if($certEligible)
      <a href="{{ route('papers.certificate', $paper) }}"
         class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-emerald-700">
        <i data-lucide="award" class="w-4 h-4"></i>
        <span>Download certificate</span>
      </a>
    @endif

    {{-- Verify (read-only) --}}
    <button type="button"
            class="w-full flex items-center gap-2 px-3 py-2 hidden rounded-md hover:bg-gray-50 text-gray-700 text-left btn-verify-chain"
            title="Verify on-chain"
            data-sha="{{ $paper->sha256 }}"
            data-title="{{ e(Str::limit($paper->title, 80)) }}">
<i data-feather="check-circle" class="w-4 h-4"></i>

      <span>Verify on-chain</span>
    </button>

    @if(auth()->user()->isAdmin())
      {{-- ADMIN: compute hash if absent --}}
      @if (!$paper->sha256)
        <form method="POST" action="{{ route('papers.hash', $paper) }}">
          @csrf
          <button type="submit"
                  class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-gray-700 text-left">
            <i data-lucide="hash" class="w-4 h-4"></i>
            <span>Compute SHA-256</span>
          </button>
        </form>
      @endif

      {{-- ADMIN: register on-chain when hashed but not registered --}}
      @if ($paper->sha256 && $paper->chain_status !== 'REGISTERED' && $paper->chain_status !== 'CONFIRMED')
        <button type="button"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-emerald-700 text-left btn-register-chain"
                title="Register on-chain"
                data-action="{{ route('papers.register', $paper) }}"
                data-confirm="{{ route('papers.confirm', $paper) }}"
                data-sha="{{ $paper->sha256 }}"
                data-title="{{ e(Str::limit($paper->title, 80)) }}"
                @if($paper->pendingRequest) data-request-id="{{ $paper->pendingRequest->id }}" @endif>
          <i data-lucide="link-2" class="w-4 h-4"></i>
          <span>Register on-chain</span>
        </button>
      @endif
    @else
      {{-- STUDENT/ADVISER: request submission (hide if pending or already registered/confirmed) --}}
      @if (!$paper->pendingRequest && $paper->chain_status !== 'REGISTERED' && $paper->chain_status !== 'CONFIRMED')
        <button type="button"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-indigo-700 text-left btn-open-request"
                title="Request blockchain submission"
                data-action="{{ route('papers.requests.store', $paper) }}"
                data-title="{{ e(Str::limit($paper->title, 80)) }}">
          <i data-lucide="send" class="w-4 h-4"></i>
          <span>Request blockchain submission</span>
        </button>
      @endif
    @endif

    {{-- Delete (opens modal) --}}
    <button type="button"
            class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-red-50 text-red-600 text-left btn-delete"
            title="Delete"
            data-action="{{ route('research-user-papers.destroy', $paper->id) }}"
            data-title="{{ e(Str::limit($paper->title, 80)) }}">
      <i data-lucide="trash-2" class="w-4 h-4"></i>
      <span>Delete</span>
    </button>
  </div>
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

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $papers->links() }}
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 backdrop-blur-sm bg-transparent" data-close-delete></div>
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
                <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-delete>Cancel</button>
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

    {{-- Details Modal --}}
    <div id="detailsModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 backdrop-blur-sm bg-transparent" data-close-modal></div>
        <div class="relative mx-auto my-8 w-full max-w-2xl bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-semibold text-gray-900">Research Paper Details</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" data-close-modal>&times;</button>
            </div>
            <div class="mt-4 space-y-4 text-sm">
                <div>
                    <p class="text-gray-500">Title</p>
                    <p id="m-title" class="font-medium text-gray-900"></p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
  <div><p class="text-gray-500">Authors</p><p id="m-authors" class="text-gray-900"></p></div>
  <div><p class="text-gray-500">Department</p><p id="m-department" class="text-gray-900"></p></div>
  <div><p class="text-gray-500">Program</p><p id="m-program" class="text-gray-900"></p></div>
  <div><p class="text-gray-500">Year</p><p id="m-year" class="text-gray-900"></p></div>
  <div><p class="text-gray-500">Uploaded</p><p id="m-uploaded" class="text-gray-900"></p></div>

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
        <div class="relative mx-auto my-8 w-full max-w-lg bg-white rounded-xl shadow-lg p-6">
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


    <!-- Request Blockchain Submission Modal -->
<div id="requestModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 backdrop-blur-sm bg-black/10" data-close-request></div>
  <div class="relative mx-auto my-8 w-full max-w-lg bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
      <h3 class="text-lg font-semibold text-gray-900">Request Blockchain Submission</h3>
      <button type="button" class="text-gray-500 hover:text-gray-700" data-close-request>&times;</button>
    </div>

    <form id="requestForm" method="POST" action="">
      @csrf
      <div class="mt-4 space-y-3 text-sm">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
          <p class="text-xs text-gray-500">Paper</p>
          <p id="req-paper-title" class="text-sm font-medium text-gray-900"></p>
        </div>
        <div class="rounded-md bg-sky-50 px-3 py-2 text-sky-700">
          This will notify the admins. You’ll see the status here as <strong>Pending</strong> until they act.
        </div>
      </div>

      <div class="mt-6 flex items-center justify-end gap-3">
        <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-request>Cancel</button>
        <button id="requestConfirmBtn" type="submit"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
          <svg id="requestSpinner" class="mr-2 hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
            <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
          </svg>
          Send Request
        </button>
      </div>
    </form>
  </div>
</div>

 
<script>
/** Toast-style notifications (top-right) */
function notify(type, title, text = '') {
  Swal.fire({
    icon: type,           // 'success' | 'error' | 'warning' | 'info' | 'question'
    title,
    text,
    toast: true,
    position: 'top-end',
    timer: 3000,
    showConfirmButton: false
  });
}

/** Full modal for longer error messages */
function modalError(title, text = '') {
  Swal.fire({
    icon: 'error',
    title,
    text,
    confirmButtonText: 'OK'
  });
}
</script>


    {{-- Styles for targetable modals (kept if you use :target elsewhere) --}}
    <style>
      .tgt-modal { display: none; }
      .tgt-modal:target { display: flex; }
    </style>

    {{-- Scripts --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    {{-- Ethers.js v6 (UMD global) --}}
    <script src="https://cdn.jsdelivr.net/npm/ethers@6.12.1/dist/ethers.umd.min.js"></script>


<script>
(() => {
  // Request modal
  const reqModal = document.getElementById('requestModal');
  const reqForm  = document.getElementById('requestForm');
  const reqTitle = document.getElementById('req-paper-title');
  const reqBtn   = document.getElementById('requestConfirmBtn');
  const reqSpin  = document.getElementById('requestSpinner');

  const openRequest = (action, title) => {
    reqForm.setAttribute('action', action);
    reqTitle.textContent = title || 'This paper';
    reqModal.classList.remove('hidden');
    document.documentElement.classList.add('overflow-hidden');
  };
  const closeRequest = () => {
    reqModal.classList.add('hidden');
    document.documentElement.classList.remove('overflow-hidden');
  };

  document.querySelectorAll('.btn-open-request').forEach(btn => {
    btn.addEventListener('click', () => openRequest(btn.dataset.action, btn.dataset.title));
  });
  reqModal.querySelectorAll('[data-close-request]').forEach(el => el.addEventListener('click', closeRequest));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !reqModal.classList.contains('hidden')) closeRequest(); });

  reqForm.addEventListener('submit', () => {
    reqBtn.disabled = true; reqSpin.classList.remove('hidden');
  });

  // Decline reason modal
  const reasonModal = document.getElementById('reasonModal');
  const reasonText  = document.getElementById('declineReasonText');
  const openReason  = (text) => {
    reasonText.textContent = text || 'No reason provided.';
    reasonModal.classList.remove('hidden');
    document.documentElement.classList.add('overflow-hidden');
  };
  const closeReason = () => {
    reasonModal.classList.add('hidden');
    document.documentElement.classList.remove('overflow-hidden');
  };

  document.querySelectorAll('.btn-view-reason').forEach(btn => {
    btn.addEventListener('click', () => openReason(btn.dataset.reason));
  });
  reasonModal.querySelectorAll('[data-close-reason]').forEach(el => el.addEventListener('click', closeReason));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !reasonModal.classList.contains('hidden')) closeReason(); });
})();
</script>







    {{-- Register (MetaMask) --}}
    <script>
    (() => {
      const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

      // Chain/Contract (Amoy)
      const CHAIN_ID_DEC = 80002;
      const CHAIN_ID_HEX = '0x13882';
      const RPC_URL      = 'https://rpc-amoy.polygon.technology';
      const CONTRACT_ADDRESS = @json(config('chain.amoy_contract', env('CONTRACT_80002', '0xYourContractHere')));
      const CONTRACT_ABI = [
        "function register(bytes32 digest) external",
        "function getTimestamp(bytes32 digest) view returns (uint256)",
        "event Registered(bytes32 indexed digest, address indexed sender, uint256 blockTime)"
      ];

      // EIP-6963-aware provider discovery (Edge-friendly)
      let discoveredProviders = [];
      window.addEventListener('eip6963:announceProvider', (event) => {
        discoveredProviders.push(event.detail.provider);
      });
      window.dispatchEvent(new Event('eip6963:requestProvider'));

      const waitForEthereum = () =>
        new Promise((resolve) => {
          if (window.ethereum) return resolve(window.ethereum);
          window.addEventListener('ethereum#initialized', () => resolve(window.ethereum), { once: true });
          setTimeout(() => resolve(window.ethereum), 1500);
        });

      async function getMetaMaskProvider() {
        const mm = discoveredProviders.find(p => p?.isMetaMask);
        if (mm) return mm;
        await waitForEthereum();
        if (window.ethereum?.isMetaMask) return window.ethereum;
        const list = window.ethereum?.providers;
        if (Array.isArray(list)) {
          const mm2 = list.find(p => p?.isMetaMask);
          if (mm2) return mm2;
        }
        return window.ethereum || null;
      }

      const toBytes32Digest = (sha256Hex) => {
        if (!sha256Hex) throw new Error('Missing sha256');
        const h = String(sha256Hex).toLowerCase().replace(/^0x/,'');
        if (h.length !== 64) throw new Error('sha256 must be 64 hex chars');
        return '0x' + h;
      };

      const ensureAmoy = async (eth) => {
        const current = await eth.request({ method: 'eth_chainId' });
        if (current === CHAIN_ID_HEX) return;
        try {
          await eth.request({ method: 'wallet_switchEthereumChain', params: [{ chainId: CHAIN_ID_HEX }] });
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

      const saveRegistrationToServer = async ({actionUrl, wallet, txHash, chainId}) => {
        await fetch(actionUrl, {
          method: 'POST',
          headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json, text/html'},
          body: JSON.stringify({ wallet, tx_hash: txHash, chain_id: chainId })
        });
      };
      const confirmOnServer = (confirmUrl) =>
        fetch(confirmUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });

      // Guard against concurrent MetaMask requests
      let MM_LOCK = false;

      document.querySelectorAll('.btn-register-chain').forEach(btn => {
        btn.addEventListener('click', async () => {
          if (MM_LOCK || btn.dataset.busy === '1') return;
          MM_LOCK = true; btn.dataset.busy = '1'; btn.disabled = true;
          btn.classList.add('opacity-50','pointer-events-none');

          try {
            if (!CONTRACT_ADDRESS || CONTRACT_ADDRESS === '0xYourContractHere') {
              throw new Error('Contract address is not configured. Set CONTRACT_80002 in .env and clear config cache.');
            }

            const digest = toBytes32Digest(btn.dataset.sha);
            const eth = await getMetaMaskProvider();
            if (!eth) throw new Error('MetaMask provider not found in Edge. Enable the extension for all sites.');

            // 1) Request accounts
            const accounts = await eth.request({ method: 'eth_requestAccounts' });
            const account  = accounts?.[0];
            if (!account) throw new Error('No account selected in MetaMask.');

            // 2) Ensure Amoy
            await ensureAmoy(eth);

            // 3) Send tx
            const provider = new ethers.BrowserProvider(eth);
            const signer   = await provider.getSigner();
            const contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer);

            const tx = await contract.register(digest);

            // 4) Save REGISTERED
            await saveRegistrationToServer({
              actionUrl: btn.dataset.action,
              wallet: account,
              txHash: tx.hash,
              chainId: CHAIN_ID_DEC
            });

            // 5) Wait 1 conf → confirm on server → reload
            const receipt = await tx.wait(1);
           if (receipt?.status === 1) {
  await confirmOnServer(btn.dataset.confirm);
  notify('success', 'Registered on-chain', 'Confirmed after 1 block.');
  location.reload();
} else {
  notify('error', 'Transaction failed or was reverted.');
}

     } catch (e) {
  const code = e?.code ?? e?.error?.code;
  const msg  = (e?.shortMessage || e?.message || '').toString();

  // 1) User cancelled in MetaMask
  if (code === 4001 || code === 'ACTION_REJECTED' || /denied|rejected/i.test(msg)) {
    notify('warning', 'Transaction cancelled', 'No changes were made.');
    return;
  }

  // 2) A MetaMask request is already open
  if (code === -32002 || /already pending/i.test(msg)) {
    notify('info', 'MetaMask request already open', 'Please check the MetaMask popup.');
    return;
  }

  // 3) Common chain errors
  if (/insufficient funds/i.test(msg)) {
    notify('error', 'Insufficient MATIC', 'Not enough balance to pay gas on Polygon Amoy.');
    return;
  }
  if (/nonce too low/i.test(msg)) {
    notify('error', 'Nonce too low', 'Try again or reset account nonce in MetaMask (Settings → Advanced).');
    return;
  }
  if (/replacement transaction underpriced/i.test(msg)) {
    notify('error', 'Underpriced replacement', 'Increase gas or try again.');
    return;
  }
  if (/network|chain/i.test(msg)) {
    notify('warning', 'Wrong network', 'Ensure MetaMask is on Polygon Amoy.');
    return;
  }

  // 4) Generic fallback
  modalError('Could not send the transaction', msg || 'Please try again.');
} finally {

            MM_LOCK = false; btn.dataset.busy = '0'; btn.disabled = false;
            btn.classList.remove('opacity-50','pointer-events-none');
          }
        }, { passive: true });
      });
    })();
    </script>

    {{-- Verify (read-only) --}}
    <script>
    (() => {
      const RPC_URL      = 'https://rpc-amoy.polygon.technology';
      const CONTRACT_ADDRESS = @json(config('chain.amoy_contract', env('CONTRACT_80002', '0xYourContractHere')));
      const CONTRACT_ABI = [
        "function register(bytes32 digest) external",
        "function getTimestamp(bytes32 digest) view returns (uint256)",
        "event Registered(bytes32 indexed digest, address indexed sender, uint256 blockTime)"
      ];

      const toBytes32Digest = (sha256Hex) => {
        if (!sha256Hex) throw new Error('Missing sha256');
        const h = String(sha256Hex).toLowerCase().replace(/^0x/,'');
        if (h.length !== 64) throw new Error('sha256 must be 64 hex chars');
        return '0x' + h;
      };

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
            const ts = await contract.getTimestamp(digest); // bigint in v6

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
  const msg = (e?.shortMessage || e?.message || e || '').toString();
  notify('error', 'Verify failed', msg || 'Failed to verify on-chain status.');
}

        }, { passive: true });
      });
    })();
    </script>


        {{-- 3-dot Actions dropdown (open/close, outside click, ESC, auto-flip if near bottom) --}}
    <script>
      (() => {
        const menus = new Map(); // id -> {menu, trigger}
        function closeAll(exceptId = null) {
          menus.forEach((v, id) => {
            if (id !== exceptId) {
              v.menu.classList.add('hidden');
              v.trigger.setAttribute('aria-expanded', 'false');
            }
          });
        }

        function ensureInViewport(menuEl) {
          // Basic auto-flip: if menu would go off-screen at bottom, open upward
          menuEl.classList.remove('origin-top-right');
          menuEl.classList.remove('origin-bottom-right');
          menuEl.style.transformOrigin = '';

          // reset position to default (down)
          menuEl.style.top = '100%';
          menuEl.style.bottom = 'auto';

          const rect = menuEl.getBoundingClientRect();
          const overBottom = rect.bottom > window.innerHeight - 8; // 8px padding
          if (overBottom) {
            // open upward
            menuEl.style.top = 'auto';
            menuEl.style.bottom = '100%';
          }
        }

        document.querySelectorAll('.paper-actions-trigger').forEach(trigger => {
          const id = trigger.getAttribute('data-menu-id');
          const menu = document.getElementById(id);
          if (!menu) return;
          menus.set(id, { menu, trigger });

          // Open/Close on click
          trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = menu.classList.contains('hidden');
            closeAll(isHidden ? id : null);
            if (isHidden) {
              menu.classList.remove('hidden');
              trigger.setAttribute('aria-expanded', 'true');
              // Slight delay so DOM paints before measuring
              requestAnimationFrame(() => ensureInViewport(menu));
            } else {
              menu.classList.add('hidden');
              trigger.setAttribute('aria-expanded', 'false');
            }
          }, { passive: true });
        });

        // Close on outside click
        document.addEventListener('click', () => closeAll(), { passive: true });

        // Close on ESC
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') closeAll();
        }, { passive: true });

        // Prevent menu click from bubbling to document
        document.querySelectorAll('.paper-actions-menu').forEach(menu => {
          menu.addEventListener('click', (e) => e.stopPropagation());
        });
      })();
    </script>


    {{-- Details + Delete modals + icons --}}
    <script>
        lucide.createIcons();

        // Details modal
        const detailsModal = document.getElementById("detailsModal");
        const detailsCloseBtns = detailsModal.querySelectorAll("[data-close-modal]");

        document.querySelectorAll(".btn-details").forEach(btn => {
            btn.addEventListener("click", () => {
                document.getElementById("m-title").textContent = btn.dataset.title;
                document.getElementById("m-authors").textContent = btn.dataset.authors;
                document.getElementById("m-department").textContent = btn.dataset.department;
                document.getElementById("m-program").textContent = btn.dataset.program;
                document.getElementById("m-year").textContent = btn.dataset.year;
                document.getElementById("m-uploaded").textContent = btn.dataset.uploaded;
                document.getElementById("m-file-url").href = btn.dataset.fileUrl;

                if (btn.dataset.abstract) {
                    document.getElementById("m-abstract").textContent = btn.dataset.abstract;
                    document.getElementById("m-abstract-wrap").classList.remove("hidden");
                } else {
                    document.getElementById("m-abstract-wrap").classList.add("hidden");
                }

                // Plagiarism badge (uses saved server value)
              const plagEl = document.getElementById("m-plag");
              let cls = "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ";
              let txt = "—";
              const raw = btn.dataset.plagiarism;
              if (raw !== undefined && raw !== "" && !Number.isNaN(Number(raw))) {
                const val = Number(raw);
                txt = `${val}%`;
                // Same threshold as upload gate
                cls += (val >= 45) ? "bg-red-100 text-red-700" : "bg-green-100 text-green-700";
              } else {
                cls += "bg-gray-100 text-gray-700";
              }
              plagEl.textContent = txt;
              plagEl.className = cls;


                detailsModal.classList.remove("hidden");
            });
        });
        detailsCloseBtns.forEach(btn => btn.addEventListener("click", () => detailsModal.classList.add("hidden")));

        // Delete modal
        const deleteModal = document.getElementById('deleteModal');
        const deleteForm  = document.getElementById('deleteForm');
        const delTitleEl  = document.getElementById('del-title');
        const confirmBtn  = document.getElementById('confirmDeleteBtn');
        const spinnerEl   = document.getElementById('confirmDeleteSpinner');

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.action;
                const title  = btn.dataset.title || 'This research paper';
                deleteForm.setAttribute('action', action);
                delTitleEl.textContent = title;
                deleteModal.classList.remove('hidden');
                document.documentElement.classList.add('overflow-hidden');
            });
        });

        deleteModal.querySelectorAll('[data-close-delete]').forEach(el => {
            el.addEventListener('click', () => {
                deleteModal.classList.add('hidden');
                document.documentElement.classList.remove('overflow-hidden');
            });
        });

        document.addEventListener('keydown', (e) => {
            if (!deleteModal.classList.contains('hidden') && e.key === 'Escape') {
                deleteModal.classList.add('hidden');
                document.documentElement.classList.remove('overflow-hidden');
            }
        });

        deleteForm.addEventListener('submit', () => {
            confirmBtn.disabled = true;
            spinnerEl.classList.remove('hidden');
        });
    </script>
</x-userlayout>
