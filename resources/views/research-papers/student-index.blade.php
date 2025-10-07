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
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">

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

                <!-- Per Page -->
<div>
  <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Per page</label>
  <select name="per_page" id="per_page"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      @php $pp = (int) request('per_page', 10); @endphp
      @foreach([10,20,50,100] as $n)
          <option value="{{ $n }}" {{ $pp === $n ? 'selected' : '' }}>{{ $n }}</option>
      @endforeach
  </select>
</div>


                <div class="md:col-span-5 flex gap-2">

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
     {{-- Table --}}
<div class="-mx-4 sm:mx-0 overflow-x-auto border border-gray-200 rounded-lg scrollbar-thin">
  <table class="min-w-[960px] w-full divide-y divide-gray-200 relative text-sm">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Authors</th>
                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>

                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($papers as $paper)
                    @php
                      $displayStatus = in_array($paper->chain_status, ['REGISTERED','CONFIRMED'], true)
                          ? 'REGISTERED'
                          : ($paper->chain_status ?? 'NONE');

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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
  @php $u = $paper->user; @endphp
  @if($u)
    <div class="flex flex-col">
      <span class="font-medium">{{ Str::limit($u->name, 30) }}</span>
      <span class="text-gray-500 text-xs">{{ Str::limit($u->email, 40) }}</span>
    </div>
  @else
    <span class="text-gray-400">—</span>
  @endif
</td>

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
                              Request: APPROVED
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
                            <a href="{{ $explorer.$tx }}" target="_blank" rel="noopener noreferrer" class="ml-2 text-xs text-indigo-600 hover:underline">View tx</a>
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

                          {{-- Dropdown (portalled to <body> on open) --}}
                          <div id="menu-{{ $paper->id }}"
                               class="paper-actions-menu hidden z-[9999] min-w-[220px] rounded-lg border border-gray-300 bg-white p-1 shadow-lg">
                            {{-- Details --}}
                            @php
  // Compute viewer URL. If the current user is the uploader, append ?view=owner
  $viewerUrl = route('papers.view', $paper);
  if (auth()->id() === optional($paper->user)->id) {
      $viewerUrl .= (str_contains($viewerUrl, '?') ? '&' : '?') . 'view=owner';
  }
@endphp

                            <button type="button"
                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-gray-700 text-left btn-details"
                                    title="Details"
                                    data-title="{{ e($paper->title) }}"
                                    data-authors="{{ e($paper->authors) }}"
                                    data-department="{{ e($paper->department) }}"
                                    data-program="{{ e($paper->program) }}"
                                    data-year="{{ e($paper->year) }}"
                                    data-uploaded="{{ $paper->created_at->format('M d, Y') }}"
                                    data-file-url="{{ $viewerUrl }}"

                                    data-abstract="{{ e($paper->abstract ?? '') }}"
                                    data-plagiarism="{{ is_null($paper->plagiarism_score) ? '' : (int)$paper->plagiarism_score }}">
                              <i data-lucide="info" class="w-4 h-4"></i>
                              <span>View details</span>
                            </button>

                            {{-- Certificate (if eligible) --}}
                            @php
                              $certEligible = in_array(($paper->chain_status ?? ''), ['REGISTERED','CONFIRMED'], true)
                                              && !empty($paper->tx_hash) && !empty($paper->sha256);
                            @endphp
                            @if($certEligible)
                              <a href="{{ route('papers.certificate', $paper) }}"
                                 class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-emerald-700">
                                <i data-lucide="award" class="w-4 h-4"></i>
                                <span>Download certificate</span>
                              </a>
                            @endif

                            {{-- Verify (read-only) — visible --}}
                            <button type="button"
                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 hidden text-gray-700 text-left btn-verify-chain"
                                    title="Verify on-chain"
                                    data-sha="{{ $paper->sha256 }}"
                                    data-title="{{ e(Str::limit($paper->title, 80)) }}">
                              <i data-lucide="check-circle" class="w-4 h-4"></i>
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
                              @if ($paper->sha256 && $paper->chain_status!=='REGISTERED' && $paper->chain_status!=='CONFIRMED')
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
                              {{-- STUDENT/ADVISER: request submission --}}
                              @if (!$paper->pendingRequest && $paper->chain_status!=='REGISTERED' && $paper->chain_status!=='CONFIRMED')
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

                            {{-- Delete --}}
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
        {{-- Pagination --}}
<div class="mt-6">
  <div class="flex items-center justify-between">
    <div class="text-xs text-gray-500">
      @if ($papers->total() > 0)
        Showing <span class="font-medium">{{ $papers->firstItem() }}</span>
        to <span class="font-medium">{{ $papers->lastItem() }}</span>
        of <span class="font-medium">{{ $papers->total() }}</span> results
      @else
        Showing <span class="font-medium">0</span> results
      @endif
    </div>
    <div>
      {{ $papers->onEachSide(1)->links() }}
    </div>
  </div>
</div>

    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
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
    <div id="detailsModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" style="z-index: 20000;">
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

                  <div class="sm:col-span-2">
                    <p class="text-gray-500">Plagiarism</p>
                    <span id="m-plag"
                          class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700">—</span>
                    <span class="ml-2 text-xs text-gray-500">(Saved score)</span>
                  </div>
                </div>

   <div id="m-abstract-wrap" class="hidden">
  <p class="text-gray-500">Abstract</p>
  <textarea id="m-abstract"
            readonly
            class="w-full mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 leading-relaxed resize-none"
            rows="8"
            style="max-height: 250px; overflow-y: auto;"></textarea>
</div>



            </div>
            <div class="mt-6 flex items-center justify-end gap-3">
                <a id="m-file-url" href="#" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                    Open PDF
                </a>
                <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-modal>Close</button>
            </div>
        </div>
    </div>

    {{-- Verify Modal --}}
    <div id="verifyModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
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
    <div id="requestModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
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
<style>
  .swal2-container { z-index: 20000 !important; }
</style>
    {{-- Toast helpers (SweetAlert assumed in layout) --}}
    <script>
      function notify(type, title, text = '') {
        Swal.fire({ icon: type, title, text, toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
      }
      function modalError(title, text = '') { Swal.fire({ icon: 'error', title, text, confirmButtonText: 'OK' }); }
      function loadingSwal(title='Working...', text='Please keep this tab focused and check MetaMask when it appears.') {
        Swal.fire({ title, text, allowOutsideClick:false, allowEscapeKey:false, didOpen: () => Swal.showLoading() });
      }
      function closeSwal(){ try{ Swal.close(); }catch(_){} }
    </script>

    {{-- Icons + Ethers --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/ethers@6.12.1/dist/ethers.umd.min.js"></script>

    {{-- 3-dot Actions dropdown (portal to <body>, fixed-position like admin) --}}
    <script>
    (() => {
      const GAP = 8;
      const menus = new Map(); // id -> { menu, trigger, origParent }

      function closeMenu(id) {
        const entry = menus.get(id);
        if (!entry) return;
        const { menu, trigger, origParent } = entry;
        menu.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
        if (menu.dataset.portalled === '1' && origParent) {
          origParent.appendChild(menu);
          delete menu.dataset.portalled;
        }
        menu.removeAttribute('style');
      }

      function closeAll(exceptId = null) {
        menus.forEach((_, id) => { if (id !== exceptId) closeMenu(id); });
      }

      function placeMenuFixed(trigger, menu) {
        const wasHidden = menu.classList.contains('hidden');
        if (wasHidden) { menu.classList.remove('hidden'); menu.style.visibility = 'hidden'; }
        menu.style.position = 'fixed';
        const t = trigger.getBoundingClientRect();
        const m = menu.getBoundingClientRect();
        let left = Math.min(Math.max(8, t.right - m.width), window.innerWidth - m.width - 8);
        let top  = t.bottom + GAP;
        if (top + m.height > window.innerHeight - 8) { top = Math.max(8, t.top - GAP - m.height); }
        menu.style.left = left + 'px';
        menu.style.top  = top  + 'px';
        if (wasHidden) menu.style.visibility = '';
      }

      document.querySelectorAll('.paper-actions-trigger').forEach(trigger => {
        const id = trigger.getAttribute('data-menu-id');
        const menu = document.getElementById(id);
        if (!menu) return;
        menus.set(id, { menu, trigger, origParent: menu.parentNode });

        trigger.addEventListener('click', (e) => {
          e.stopPropagation();
          if (trigger.dataset.debounce === '1') return;
          trigger.dataset.debounce = '1'; setTimeout(()=> trigger.dataset.debounce='0', 250);

          const willOpen = menu.classList.contains('hidden');
          closeAll(willOpen ? id : null);
          if (willOpen) {
            trigger.setAttribute('aria-expanded', 'true');
            if (menu.dataset.portalled !== '1') {
              document.body.appendChild(menu);
              menu.dataset.portalled = '1';
            }
            placeMenuFixed(trigger, menu);
            menu.classList.remove('hidden');
          } else {
            closeMenu(id);
          }
        }, { passive: true });
      });

      function repositionOpenMenus() {
        menus.forEach(({ menu, trigger }) => {
          if (!menu.classList.contains('hidden')) placeMenuFixed(trigger, menu);
        });
      }
      window.addEventListener('resize', repositionOpenMenus, { passive: true });
      window.addEventListener('scroll', repositionOpenMenus, { passive: true });

      document.addEventListener('click', (e) => {
        if (e.target.closest('.paper-actions-menu')) return;
        if (e.target.closest('.paper-actions-trigger')) return;
        closeAll();
      }, { passive: true });

      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); }, { passive: true });
    })();
    </script>

    {{-- Request modal wiring --}}
    <script>
    (() => {
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
        reqBtn.disabled = false; reqSpin.classList.add('hidden');
      };

      document.querySelectorAll('.btn-open-request').forEach(btn => {
        btn.addEventListener('click', () => openRequest(btn.dataset.action, btn.dataset.title));
      });
      reqModal.querySelectorAll('[data-close-request]').forEach(el => el.addEventListener('click', closeRequest));
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !reqModal.classList.contains('hidden')) closeRequest(); });

      reqForm.addEventListener('submit', () => { reqBtn.disabled = true; reqSpin.classList.remove('hidden'); });
    })();
    </script>

    {{-- Delete modal wiring --}}
    <script>
    (() => {
      const deleteModal = document.getElementById('deleteModal');
      const deleteForm  = document.getElementById('deleteForm');
      const delTitleEl  = document.getElementById('del-title');
      const confirmBtn  = document.getElementById('confirmDeleteBtn');
      const spinnerEl   = document.getElementById('confirmDeleteSpinner');

      const openDelete = (action, title) => {
        deleteForm.setAttribute('action', action);
        delTitleEl.textContent = title || 'This research paper';
        deleteModal.classList.remove('hidden');
        document.documentElement.classList.add('overflow-hidden');
      };
      const closeDelete = () => {
        deleteModal.classList.add('hidden');
        document.documentElement.classList.remove('overflow-hidden');
        confirmBtn.disabled = false; spinnerEl.classList.add('hidden');
      };

      document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => openDelete(btn.dataset.action, btn.dataset.title));
      });
      deleteModal.querySelectorAll('[data-close-delete]').forEach(el => el.addEventListener('click', closeDelete));
      document.addEventListener('keydown', (e) => { if (!deleteModal.classList.contains('hidden') && e.key === 'Escape') closeDelete(); });
      deleteForm.addEventListener('submit', () => { confirmBtn.disabled = true; spinnerEl.classList.remove('hidden'); });
    })();
    </script>

    {{-- Details modal wiring (with plagiarism badge) --}}
    <script>
    (() => {
      const modal = document.getElementById('detailsModal');

      const setText = (selector, value, fallback = '—') => {
        const el = document.querySelector(selector);
        if (!el) return;
        el.textContent = (value && String(value).trim() !== '') ? value : fallback;
      };

      const openFromBtn = (btn) => {
        setText('#m-title', btn.dataset.title);
        setText('#m-authors', btn.dataset.authors);
        setText('#m-department', btn.dataset.department);
        setText('#m-program', btn.dataset.program);
        setText('#m-year', btn.dataset.year);
        setText('#m-uploaded', btn.dataset.uploaded);

        const link = document.getElementById('m-file-url');
        link.setAttribute('href', btn.dataset.fileUrl || '#');

      const abstract = btn.dataset.abstract || '';
const wrap = document.getElementById('m-abstract-wrap');
const abEl = document.getElementById('m-abstract'); // textarea

if (abstract.trim()) {
  abEl.value = abstract;
  wrap.classList.remove('hidden');
} else {
  abEl.value = '';
  wrap.classList.add('hidden');
}



        const plagEl = document.getElementById('m-plag');
        let cls = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ';
        let txt = '—';
        const raw = btn.dataset.plagiarism;
        if (raw !== undefined && raw !== '' && !Number.isNaN(Number(raw))) {
          const val = Number(raw);
          txt = `${val}%`;
          cls += (val >= 45) ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
        } else {
          cls += 'bg-gray-100 text-gray-700';
        }
        plagEl.textContent = txt; plagEl.className = cls;

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      };

      document.querySelectorAll('.btn-details').forEach(btn => {
        btn.addEventListener('click', (e) => { e.preventDefault(); openFromBtn(btn); });
      });

      modal.querySelectorAll('[data-close-modal]').forEach(el => {
        el.addEventListener('click', () => { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); });
      });
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }});
      modal.querySelector('.absolute.inset-0')?.addEventListener('click', () => { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); });
    })();
    </script>

    {{-- Verify (read-only) --}}
    <script>
    (() => {
      const RPC_URL = 'https://rpc-amoy.polygon.technology';
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
            const msg = (e?.shortMessage || e?.message || e || '').toString();
            notify('error', 'Verify failed', msg || 'Failed to verify on-chain status.');
          }
        }, { passive:true });
      });
    })();
    </script>

    {{-- Register (MetaMask) — resilient flow with fallback re-check --}}
    <script>
    (() => {
      const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

      const CHAIN_ID_DEC = 80002;
      const CHAIN_ID_HEX = '0x13882';
      const RPC_URL      = 'https://rpc-amoy.polygon.technology';
      const EXPLORER_TX  = 'https://amoy.polygonscan.com/tx/';
      const CONTRACT_ADDRESS = @json(config('chain.amoy_contract', env('CONTRACT_80002', '0xYourContractHere')));
      const CONTRACT_ABI = [
        "function register(bytes32 digest) external",
        "function getTimestamp(bytes32 digest) view returns (uint256)",
        "event Registered(bytes32 indexed digest, address indexed sender, uint256 blockTime)"
      ];

      function normalizeEthersMessage(err) {
        const msg = err?.shortMessage || err?.reason || err?.info?.error?.message || err?.error?.message || err?.message || '';
        return String(msg);
      }

      async function robustWaitForConfirm(provider, txHash, confirmations = 1, timeoutMs = 120000) {
        try {
          return await provider.waitForTransaction(txHash, confirmations);
        } catch (_) {
          const start = Date.now();
          while (Date.now() - start < timeoutMs) {
            const rec = await provider.getTransactionReceipt(txHash);
            if (rec && rec.blockNumber != null) {
              const tip = await provider.getBlockNumber();
              const confs = Math.max(0, tip - rec.blockNumber + 1);
              if (confs >= confirmations) return rec;
            }
            await new Promise(r => setTimeout(r, 2000));
          }
          throw new Error('Timeout while confirming transaction.');
        }
      }

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

      const toBytes32Digest = (sha256Hex) => {
        if (!sha256Hex) throw new Error('Missing sha256');
        const h = String(sha256Hex).toLowerCase().replace(/^0x/, '');
        if (h.length !== 64) throw new Error('sha256 must be 64 hex chars');
        return '0x' + h;
      };

      const saveRegistrationToServer = ({actionUrl, wallet, txHash, chainId}) =>
        fetch(actionUrl, {
          method: 'POST',
          headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json, text/html'},
          body: JSON.stringify({ wallet, tx_hash: txHash, chain_id: chainId })
        });

      const confirmOnServer = (confirmUrl) =>
        fetch(confirmUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });

      let MM_LOCK = false;
      let MM_REQ_LOCK = false;

      document.querySelectorAll('.btn-register-chain').forEach(btn => {
        btn.addEventListener('click', async () => {
          if (MM_LOCK || btn.dataset.busy === '1') return;
          MM_LOCK = true; btn.dataset.busy = '1'; btn.disabled = true;

          let txHash = '';
          let confirmUrl = '';
          try {
            if (!ethers.isAddress(CONTRACT_ADDRESS)) {
              throw new Error('Contract misconfigured. Set a valid address in env (CONTRACT_80002).');
            }

            const digest     = toBytes32Digest(btn.dataset.sha);
            const actionUrl  = btn.dataset.action || '';
            confirmUrl       = btn.dataset.confirm || '';

            // Connect MetaMask (serialize requests)
            const eth = await getMetaMaskProvider();
            if (!eth) throw new Error('MetaMask provider not found. Enable the extension.');

            let accounts;
            try {
              if (MM_REQ_LOCK) throw { code: -32002, message: 'Request already pending' };
              MM_REQ_LOCK = true;
              loadingSwal('Connecting to MetaMask...', 'Please confirm the account request in MetaMask');
              accounts = await eth.request({ method: 'eth_requestAccounts' });
            } finally {
              MM_REQ_LOCK = false; closeSwal();
            }
            const account = accounts?.[0];
            if (!account) throw new Error('No account selected in MetaMask.');

            // Ensure network
            loadingSwal('Switching network...', 'Ensuring Polygon Amoy (80002)');
            await ensureAmoy(eth); closeSwal();

            // Send tx
            loadingSwal('Sending transaction...', 'Registering digest on-chain');
            const provider = new ethers.BrowserProvider(eth);
            const signer   = await provider.getSigner();
            const contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer);
            const tx       = await contract.register(digest);
            txHash = tx?.hash || '';
            closeSwal();

            // Save server-side
            loadingSwal('Saving...', 'Recording transaction on server');
            try {
              await saveRegistrationToServer({ actionUrl, wallet: account, txHash, chainId: CHAIN_ID_DEC });
            } finally { closeSwal(); }

            // Confirm with robust fallback
            loadingSwal('Confirming...', 'Waiting for 1 block confirmation');
            const receipt = await robustWaitForConfirm(provider, txHash, 1, 120000);
            closeSwal();

            if (receipt?.status === 1) {
              await confirmOnServer(confirmUrl);
              notify('success', 'Registered on-chain', 'Confirmed after 1 block.');
              location.reload();
            } else {
              notify('error', 'Transaction failed or was reverted.');
            }
          } catch (e) {
            closeSwal();
            const code = e?.code ?? e?.error?.code;
            const msg  = normalizeEthersMessage(e);

            if (code === 4001 || code === 'ACTION_REJECTED' || /denied|rejected/i.test(msg)) { notify('warning','Transaction cancelled','No changes were made.'); return; }
            if (code === -32002 || /already pending/i.test(msg)) { notify('info','MetaMask request already open','Please check the MetaMask popup.'); return; }
            if (/sha256|64 hex|bytes32/i.test(msg)) { modalError('Invalid digest','Digest must be a 64-character sha256 hex string.'); return; }
            if (/insufficient funds/i.test(msg)) { notify('error','Insufficient MATIC','Not enough balance on Polygon Amoy.'); return; }
            if (/nonce too low/i.test(msg)) { notify('error','Nonce too low','Try again or reset account nonce in MetaMask.'); return; }
            if (/replacement transaction underpriced/i.test(msg)) { notify('error','Underpriced replacement','Increase gas or try again.'); return; }
            if (/network|chain/i.test(msg)) { notify('warning','Wrong network','Ensure MetaMask is on Polygon Amoy.'); return; }

            // If we have a tx hash, try to recover by re-checking status (read-only provider)
            if (txHash) {
              try {
                loadingSwal('Re-checking...', 'Verifying transaction status');
                const ro = new ethers.JsonRpcProvider(RPC_URL);
                const rec = await robustWaitForConfirm(ro, txHash, 1, 120000);
                closeSwal();
                if (rec?.status === 1 && confirmUrl) {
                  await confirmOnServer(confirmUrl);
                  notify('success','Registered on-chain','Confirmed after re-check.');
                  location.reload();
                  return;
                }
              } catch (_) { /* fall through */ }
              closeSwal();
              Swal.fire({
                icon: 'info',
                title: 'Transaction sent — confirming',
                html: `We sent the transaction but couldn’t verify immediately.<br>
                       <a href="${EXPLORER_TX + txHash}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 underline">View on Polygonscan</a>`,
              });
              return;
            }

            modalError('Could not send the transaction', msg || 'Please try again.');
          } finally {
            MM_LOCK = false; btn.dataset.busy = '0'; btn.disabled = false;
          }
        }, { passive:true });
      });
    })();
    </script>

    <script> lucide.createIcons(); </script>
</x-userlayout>
