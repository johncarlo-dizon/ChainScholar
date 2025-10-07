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
          <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">


                <!-- Search -->
                <div class="md:col-span-3">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Search papers...">
                </div>

                <!-- User Filter -->
             <div class="md:col-span-2">

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
          <div class="md:col-span-2">

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
               <div class="md:col-span-2">

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
           <div class="md:col-span-1">

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
<div class="md:col-span-2">

  <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Per page</label>
  <select name="per_page" id="per_page"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      @php $pp = (int) request('per_page', 10); @endphp
      @foreach([10,20,50,100] as $n)
          <option value="{{ $n }}" {{ $pp === $n ? 'selected' : '' }}>{{ $n }}</option>
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
<div class="border border-gray-200 rounded-lg overflow-visible">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 relative">

       <thead class="bg-gray-50">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Authors</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>

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

  {{-- Dropdown --}}
<div id="menu-{{ $paper->id }}"
     class="paper-actions-menu hidden z-[9999] min-w-[220px] rounded-lg border border-gray-300  bg-white p-1 shadow-lg">


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
            data-user-name="{{ e(optional($paper->user)->name) }}"
            data-user-email="{{ e(optional($paper->user)->email) }}"
            data-uploaded="{{ $paper->created_at->format('M d, Y') }}"
            data-file-url="{{ $viewerUrl }}"


            data-abstract="{{ e($paper->abstract ?? '') }}"
            data-plagiarism="{{ is_null($paper->plagiarism_score) ? '' : (int)$paper->plagiarism_score }}">
      <i data-feather="info" class="w-4 h-4"></i>
      <span>View details</span>
    </button>

    {{-- Verify (read-only) --}}
    <button type="button"
            class="w-full flex items-center hidden gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-gray-700 text-left btn-verify-chain"
            title="Verify on-chain"
            data-sha="{{ $paper->sha256 }}"
            data-title="{{ e(Str::limit($paper->title, 80)) }}">
    <i data-feather="check-circle" class="w-4 h-4"></i>

      <span>Verify on-chain</span>
    </button>

    {{-- Certificate (if eligible) --}}
    @php
      $certEligible = in_array(($paper->chain_status ?? ''), ['REGISTERED','CONFIRMED'], true)
                      && !empty($paper->tx_hash) && !empty($paper->sha256);
    @endphp
    @if($certEligible)
      <a href="{{ route('papers.certificate', $paper) }}"
         class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-emerald-700">
        <i data-feather="award" class="w-4 h-4"></i>
        <span>Download certificate</span>
      </a>
    @endif

    @if(auth()->user()->isAdmin())
      {{-- Admin: compute hash if absent --}}
      @if (!$paper->sha256)
        <form method="POST" action="{{ route('papers.hash', $paper) }}">
          @csrf
          <button type="submit"
                  class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-gray-700 text-left">
            <i data-feather="hash" class="w-4 h-4"></i>
            <span>Compute SHA-256</span>
          </button>
        </form>
      @endif

      {{-- Admin: Approve & Register (if pending request) --}}
      @if ($paper->pendingRequest && $paper->sha256 && $paper->chain_status!=='REGISTERED' && $paper->chain_status!=='CONFIRMED')
        <button type="button"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-emerald-700 text-left btn-register-chain"
                title="Approve & Register"
                data-request-id="{{ $paper->pendingRequest->id }}"
                data-approve="{{ route('requests.approve', $paper->pendingRequest->id) }}"
                data-action="{{ route('papers.register', $paper) }}"
                data-confirm="{{ route('papers.confirm', $paper) }}"
                data-sha="{{ $paper->sha256 }}"
                data-title="{{ e(Str::limit($paper->title, 80)) }}">
          <i data-feather="check-circle" class="w-4 h-4"></i>
          <span>Approve & Register</span>
        </button>

        {{-- Decline with reason --}}
        <button type="button"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-red-50 text-rose-600 text-left btn-decline-request"
                data-decline="{{ route('requests.decline', $paper->pendingRequest->id) }}"
                data-paper-title="{{ e(Str::limit($paper->title, 80)) }}">
          <i data-feather="x-circle" class="w-4 h-4"></i>
          <span>Decline request</span>
        </button>
      @endif

      {{-- Admin: Plain Register (no request) --}}
      @if (!$paper->pendingRequest && $paper->sha256 && $paper->chain_status!=='REGISTERED' && $paper->chain_status!=='CONFIRMED')
        <button type="button"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-50 text-emerald-700 text-left btn-register-chain"
                title="Register on-chain"
                data-action="{{ route('papers.register', $paper) }}"
                data-confirm="{{ route('papers.confirm', $paper) }}"
                data-sha="{{ $paper->sha256 }}"
                data-title="{{ e(Str::limit($paper->title, 80)) }}">
          <i data-feather="link-2" class="w-4 h-4"></i>
          <span>Register on-chain</span>
        </button>
      @endif
    @endif

    {{-- Delete --}}
    <button type="button"
            class="w-full flex items-center gap-2 px-3 py-2 rounded-md hover:bg-red-50 text-red-600 text-left btn-delete"
            title="Delete"
            data-action="{{ route('research-papers.destroy', $paper->id) }}"
            data-title="{{ e(Str::limit($paper->title, 80)) }}">
      <i data-feather="trash-2" class="w-4 h-4"></i>
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
</div>

        <!-- Pagination -->
     <!-- Pagination -->
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
<div id="detailsModal" class="fixed inset-0 hidden" style="z-index: 20000;">
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
  <textarea id="m-abstract"
            readonly
            class="w-full mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 leading-relaxed resize-none"
            rows="8"
            style="max-height: 250px; overflow-y: auto;"></textarea>
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
<div id="approveModal" class="fixed inset-0   hidden" style="z-index: 20000 !important;">
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

<!-- 3-dot Actions dropdown (portal to <body>, fixed-position) -->
<script>
(() => {
  const GAP = 8; // px between trigger and menu
  const menus = new Map(); // id -> { menu, trigger, origParent }

  function closeMenu(id) {
    const entry = menus.get(id);
    if (!entry) return;
    const { menu, trigger, origParent } = entry;

    menu.classList.add('hidden');
    trigger.setAttribute('aria-expanded', 'false');

    // put it back where it came from (keeps DOM tidy for pagination/rerenders)
    if (menu.dataset.portalled === '1' && origParent) {
      origParent.appendChild(menu);
      delete menu.dataset.portalled;
    }
    menu.removeAttribute('style'); // clears left/top/position/visibility
  }

  function closeAll(exceptId = null) {
    menus.forEach((_, id) => { if (id !== exceptId) closeMenu(id); });
  }

  function placeMenuFixed(trigger, menu) {
    // show invisibly to measure
    const wasHidden = menu.classList.contains('hidden');
    if (wasHidden) {
      menu.classList.remove('hidden');
      menu.style.visibility = 'hidden';
    }

    menu.style.position = 'fixed';

    const t = trigger.getBoundingClientRect();
    const m = menu.getBoundingClientRect();

    // right-align to trigger; open downward by default
    let left = Math.min(Math.max(8, t.right - m.width), window.innerWidth - m.width - 8);
    let top  = t.bottom + GAP;

    // flip upward if we don't have space
    if (top + m.height > window.innerHeight - 8) {
      top = Math.max(8, t.top - GAP - m.height);
    }

    menu.style.left = left + 'px';
    menu.style.top  = top  + 'px';

    if (wasHidden) menu.style.visibility = '';
  }

  // Wire triggers
  document.querySelectorAll('.paper-actions-trigger').forEach(trigger => {
    const id = trigger.getAttribute('data-menu-id');
    const menu = document.getElementById(id);
    if (!menu) return;

    menus.set(id, { menu, trigger, origParent: menu.parentNode });

    trigger.addEventListener('click', (e) => {
      e.stopPropagation(); // don't fall through to doc click
      if (trigger.dataset.debounce === '1') return;
      trigger.dataset.debounce = '1';
      setTimeout(() => { trigger.dataset.debounce = '0'; }, 250);

      const willOpen = menu.classList.contains('hidden');

      closeAll(willOpen ? id : null);
      if (willOpen) {
        trigger.setAttribute('aria-expanded', 'true');

        // Portal to <body> so it's above all table rows/overflow
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

  // Reposition any open menus on scroll/resize
  function repositionOpenMenus() {
    menus.forEach(({ menu, trigger }) => {
      if (!menu.classList.contains('hidden')) placeMenuFixed(trigger, menu);
    });
  }
  window.addEventListener('resize', repositionOpenMenus, { passive: true });
  window.addEventListener('scroll', repositionOpenMenus, { passive: true });

  // CLOSE on outside click — but ignore clicks inside menu or on a trigger
  document.addEventListener('click', (e) => {
    if (e.target.closest('.paper-actions-menu')) return;     // clicked inside menu
    if (e.target.closest('.paper-actions-trigger')) return;  // clicked a trigger
    closeAll();
  }, { passive: true });

  // ESC to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAll();
  }, { passive: true });
})();
</script>






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
    abstractEl.value = abstract;
    abstractWrap.classList.remove('hidden');
} else {
    abstractEl.value = '';
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

 <style>
  .swal2-container { z-index: 20000 !important; }
</style>

<script>
/** Toast-style notifications (top-right) */
function notify(type, title, text = '') {
  Swal.fire({
    icon: type, // 'success' | 'error' | 'warning' | 'info' | 'question'
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

// --- Loading overlays using SweetAlert ---
function loadingSwal(title = 'Working...', text = 'Please keep this tab focused and check MetaMask when it appears.') {
  Swal.fire({
    title,
    text,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => Swal.showLoading()
  });
}
function closeSwal() { try { Swal.close(); } catch(_) {} }

</script>

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


  // Explorer (Amoy)
const EXPLORER_TX = 'https://amoy.polygonscan.com/tx/';

// Extract a meaningful ethers v6 message
function normalizeEthersMessage(err) {
  const msg =
    err?.shortMessage ||
    err?.reason ||
    err?.info?.error?.message ||
    err?.error?.message ||
    err?.message ||
    '';
  return String(msg);
}

// Wait with fallback polling to survive flaky RPCs
async function robustWaitForConfirm(provider, txHash, confirmations = 1, timeoutMs = 120000) {
  // Try native waiter first (ethers v6 ignores extra args; no timeout param)
  try {
    return await provider.waitForTransaction(txHash, confirmations);
  } catch (_) {
    // Manual polling fallback
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
let MM_REQ_LOCK = false; // protects eth_requestAccounts
let MM_TX_INFLIGHT = false; // future use if you queue multiple txs


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
  const rawSha = (btn.dataset.sha || '').trim();

  // Show raw digest (validate on Confirm so the modal never crashes)
  apprTitleEl.textContent  = title;
  apprDigestEl.textContent = rawSha ? ('0x' + rawSha.replace(/^0x/, '').toLowerCase()) : '—';

  // stash all needed data on the confirm button
  approveConfirmBtn.dataset.action     = btn.dataset.action || '';
  approveConfirmBtn.dataset.confirm    = btn.dataset.confirm || '';
  approveConfirmBtn.dataset.approve    = btn.dataset.approve || '';
  approveConfirmBtn.dataset.sha        = rawSha || '';
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
    try {
      openApprove(btn);
    } catch (err) {
      const msg = (err?.message || '').toString();
      modalError('Invalid digest', msg || 'Digest must be a 64-character sha256 hex string.');
    }
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

  let txHash = '';
  // HOIST THESE so catch/finally can use them
  let actionUrl = '';
  let confirmUrl = '';
  let approveUrl = '';
  let hasRequest = false;

  try {
    const digest = toBytes32Digest(approveConfirmBtn.dataset.sha);

    // assign after hoist
    actionUrl  = approveConfirmBtn.dataset.action  || '';
    confirmUrl = approveConfirmBtn.dataset.confirm || '';
    approveUrl = approveConfirmBtn.dataset.approve || '';
    hasRequest = !!approveConfirmBtn.dataset.requestId;

    // ... rest of your logic ...


    // Close the modal so MM can pop nicely
    closeApprove();

    // (1) If pending request, approve server-side first
    if (hasRequest && approveUrl) {
      loadingSwal('Approving request...', 'Updating server status');
      await fetch(approveUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
      closeSwal();
    }

    // (2) Connect MetaMask (serialize the request)
    loadingSwal('Connecting to MetaMask...', 'Please confirm the account request in MetaMask');
    const eth = await getMetaMaskProvider();
    if (!eth) throw new Error('MetaMask provider not found. Enable the extension.');

    let accounts;
    try {
      if (MM_REQ_LOCK) throw { code: -32002, message: 'Request already pending' };
      MM_REQ_LOCK = true;
      accounts = await eth.request({ method: 'eth_requestAccounts' });
    } catch (err) {
      const code = err?.code ?? err?.error?.code;
      const msg  = (err?.message || err?.error?.message || '').toString();
      if (code === -32002 || /already pending/i.test(msg)) {
        closeSwal();
        notify('info', 'MetaMask request already open', 'Check the MetaMask popup (may be behind other windows).');
        return;
      }
      throw err;
    } finally {
      MM_REQ_LOCK = false;
      closeSwal();
    }

    const account = accounts?.[0];
    if (!account) throw new Error('No account selected in MetaMask.');

    // (3) Ensure network
    loadingSwal('Switching network...', 'Ensuring Polygon Amoy (80002)');
    await ensureAmoy(eth);
    closeSwal();

    // (4) Send transaction
    // (4) Send transaction (capture txHash early)
loadingSwal('Sending transaction...', 'Registering digest on-chain');
const provider = new ethers.BrowserProvider(eth);
const signer   = await provider.getSigner();
const contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer);
const tx       = await contract.register(digest);
txHash = tx?.hash || '';
closeSwal();

// (5) Save tx details server-side (best-effort)
loadingSwal('Saving...', 'Recording transaction on server');
try {
  await saveRegistrationToServer({
    actionUrl, wallet: account, txHash, chainId: CHAIN_ID_DEC
  });
} finally { closeSwal(); }

// (6) Confirm with robust fallback (handles flaky RPCs)
loadingSwal('Confirming...', 'Waiting for 1 block confirmation');
const receipt = await robustWaitForConfirm(provider, txHash, 1, 120000);
closeSwal();

if (receipt?.status === 1) {
  await confirmOnServer(confirmUrl);
  Swal.fire({
    icon: 'success',
    title: 'Registered on-chain',
    text: 'Confirmed after 1 block.',
    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
  });
  location.reload();
} else {
  notify('error', 'Transaction failed or was reverted.');
}

  } catch (e) {
  closeSwal();
  const code = e?.code ?? e?.error?.code;
  const msg  = normalizeEthersMessage(e);


    // User cancelled
    if (code === 4001 || code === 'ACTION_REJECTED' || /denied|rejected/i.test(msg)) {
      notify('warning', 'Transaction cancelled', 'No changes were made.');
      return;
    }
    // Already pending
    if (code === -32002 || /already pending/i.test(msg)) {
      notify('info', 'MetaMask request already open', 'Please check the MetaMask popup.');
      return;
    }
    // Invalid digest
    if (/sha256|64 hex|bytes32/i.test(msg)) {
      modalError('Invalid digest', 'Digest must be a 64-character sha256 hex string.');
      return;
    }
    // Common chain errors
    if (/insufficient funds/i.test(msg))     { notify('error', 'Insufficient MATIC', 'Not enough balance on Polygon Amoy.'); return; }
    if (/nonce too low/i.test(msg))          { notify('error', 'Nonce too low', 'Try again or reset account nonce (MetaMask → Settings → Advanced).'); return; }
    if (/replacement transaction underpriced/i.test(msg)) { notify('error', 'Underpriced replacement', 'Increase gas or try again.'); return; }
    if (/network|chain/i.test(msg))          { notify('warning', 'Wrong network', 'Ensure MetaMask is on Polygon Amoy.'); return; }

// If we have a tx hash, try to recover by re-checking status.
if (txHash) {
  try {
    loadingSwal('Re-checking...', 'Verifying transaction status');
    // Use a plain RPC provider for a clean read
    const reProvider = new ethers.JsonRpcProvider(RPC_URL);
    const rec = await robustWaitForConfirm(reProvider, txHash, 1, 120000);
    closeSwal();
    if (rec?.status === 1) {
      await confirmOnServer(confirmUrl);
      notify('success', 'Registered on-chain', 'Confirmed after re-check.');
      location.reload();
      return;
    }
  } catch (_) {
    // fall through to the info dialog below
  }
  closeSwal();
  Swal.fire({
    icon: 'info',
    title: 'Transaction sent — confirming',
    html: `We sent the transaction but couldn’t verify immediately.<br>
           <a href="${EXPLORER_TX + txHash}" target="_blank" class="text-indigo-600 underline">View on Polygonscan</a>`,
  });
  return;
}

    // Fallback
    modalError('Could not send the transaction', msg || 'Please try again.');
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
  const msg = (e?.shortMessage || e?.message || e || '').toString();
  notify('error', 'Verify failed', msg || 'Failed to verify on-chain status.');
}


      }, { passive:true });
    });
  })();
})();
</script>


</x-userlayout>