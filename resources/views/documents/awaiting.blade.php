<x-userlayout>
  


  <div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Pending Titles</h2>
    </div>
   
  </div>
</div>

  <div class="container mx-auto px-4 py-4">
    {{-- FILTERS / SEARCH --}}
    <form method="GET" class="bg-white border border-gray-200 rounded-xl p-3 md:p-4 mb-4">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        {{-- Search (title or adviser) --}}
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-gray-600 mb-1" for="q">Search</label>
          <input id="q" name="q" value="{{ old('q', $q ?? '') }}"
                 placeholder="Search by title or adviser name"
                 class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        {{-- Status --}}
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1" for="status">Status</label>
          <select id="status" name="status"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-400">
            @php $statusVal = $status ?? 'all'; @endphp
            <option value="all" @selected($statusVal==='all')>All</option>
            <option value="awaiting_adviser" @selected($statusVal==='awaiting_adviser')>Waiting for Adviser</option>
            <option value="awaiting_admin" @selected($statusVal==='awaiting_admin')>Waiting for Admin</option>
          </select>
        </div>

    

        {{-- Filter by Adviser (pending target) --}}
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1" for="adviser_id">Adviser</label>
          <select id="adviser_id" name="adviser_id"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <option value="">Any adviser</option>
            @foreach($advisers as $a)
              <option value="{{ $a->id }}" @selected(($advId ?? null) == $a->id)>{{ $a->name }}</option>
            @endforeach
          </select>
        </div>

            {{-- Pending with Adviser (student-initiated request exists) --}}
        <div>
         
           <label class="block text-xs font-medium text-gray-600 mb-1" for="adviser_id">Actions</label>
          <a href="{{ route('titles.awaiting') }}"
             class="px-3 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-100">Reset</a>
          <button type="submit"
                  class="px-3 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">Apply</button>
   
        </div>
      </div>
 
    </form>

    {{-- UNIFIED LIST --}}
    @php
      $pageItems = $titles instanceof \Illuminate\Pagination\AbstractPaginator
          ? $titles->getCollection()
          : collect($titles);

      $statusStyles = [
        'awaiting_adviser' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
        'awaiting_admin'   => 'bg-blue-50 text-blue-800 ring-1 ring-blue-200',
      ];
    @endphp

    @if($pageItems->isEmpty())
      <div class="bg-white border border-gray-200 rounded-lg p-6 text-sm text-gray-600 text-center">
        No results found.
      </div>
    @else
      <ul class="space-y-3">
        @foreach($pageItems as $t)
          @php
            $studentPending = $t->adviserRequests->firstWhere('requested_by', 'student');
            $incomingFromAdvisers = $t->adviserRequests->where('requested_by', 'adviser')->values();
            $badgeClass = $statusStyles[$t->status] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200';

            // Prebuild meta for <option> embedding
            $buildMeta = function($adv) {
              $p = $adv->adviserProfile;
              return [
                'id'   => $adv->id,
                'name' => $adv->name,
                'avatar' => $adv->avatar ? asset('storage/avatars/'.$adv->avatar) : asset('storage/avatars/default.png'),
                'profile' => $p ? [
                  'department'         => $p->department,
                  'field_of_expertise' => $p->field_of_expertise,
                  'highest_degree'     => $p->highest_degree,
                  'degree_school'      => $p->degree_school,
                  'degree_year'        => $p->degree_year,
                  'advisory_years'     => $p->advisory_years,
                  'projects_handled'   => $p->projects_handled,
                  'notes'              => $p->notes,
                  'achievements'       => $p->achievements->map(fn($a)=>[
                      'title'=>$a->title, 'issuer'=>$a->issuer, 'year'=>$a->year, 'description'=>$a->description
                  ])->values(),
                  'interests'          => $p->researchInterests->pluck('name')->values(),
                ] : null,
              ];
            };
          @endphp

         

<li class="bg-white border border-gray-200 rounded-lg p-4">
  {{-- TOP ROW: title/badges on the left, adviser actions on the right --}}
  <div class="flex items-start justify-between gap-4">
    <div class="min-w-0">
      <div class="font-semibold text-gray-800 text-sm md:text-base truncate">{{ $t->title }}</div>
      <div class="mt-1 flex flex-wrap items-center gap-2">
        {{-- unified status badge --}}
        @php
          $badgeClass = ($t->status === 'awaiting_adviser')
            ? 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200'
            : (($t->status === 'awaiting_admin')
              ? 'bg-blue-50 text-blue-800 ring-1 ring-blue-200'
              : 'bg-gray-50 text-gray-700 ring-1 ring-gray-200');
        @endphp
        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium {{ $badgeClass }}">
          {{ $t->status }}
        </span>

        {{-- “Pending with Adviser” badge --}}
        @if($studentPending)
          <span class="px-2 py-0.5 rounded-full text-[11px] bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200">
            Pending with <b>{{ $studentPending->adviser->name }}</b>
          </span>
        @endif
      </div>
    </div>

    {{-- RIGHT: change adviser + per-row toggle --}}
  {{-- RIGHT: toggle + (conditionally) change adviser --}}
@php $isAwaitingAdviser = ($t->status === 'awaiting_adviser'); @endphp
<div class="shrink-0 w-full sm:w-auto">
  <div class="flex items-center justify-end gap-3">
    {{-- toggle --}}
        @if($isAwaitingAdviser)
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-400"
             id="toggle-details-{{ $t->id }}">
      <span class="text-xs text-gray-700">Show details</span>
    </label>
    @endif

    {{-- change adviser (show ONLY while awaiting_adviser) --}}
    @if($isAwaitingAdviser)
      <div class="flex items-center gap-2">
        <label for="adviser_id_{{ $t->id }}" class="text-xs text-gray-600 hidden sm:inline">Change Adviser</label>
        <select id="adviser_id_{{ $t->id }}"
                class="border-gray-300 rounded-md text-sm px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-400">
          <option value="">— Select adviser —</option>
          @foreach($advisers as $a)
            @php
              $p = $a->adviserProfile;
              $meta = [
                'id'   => $a->id,
                'name' => $a->name,
                'avatar' => $a->avatar ? asset('storage/avatars/'.$a->avatar) : asset('storage/avatars/default.png'),
                'profile' => $p ? [
                  'department'         => $p->department,
                  'field_of_expertise' => $p->field_of_expertise,
                  'highest_degree'     => $p->highest_degree,
                  'degree_school'      => $p->degree_school,
                  'degree_year'        => $p->degree_year,
                  'advisory_years'     => $p->advisory_years,
                  'projects_handled'   => $p->projects_handled,
                  'notes'              => $p->notes,
                  'achievements'       => $p->achievements->map(fn($aa)=>[
                      'title'=>$aa->title, 'issuer'=>$aa->issuer, 'year'=>$aa->year, 'description'=>$aa->description
                  ])->values(),
                  'interests'          => $p->researchInterests->pluck('name')->values(),
                ] : null,
              ];
            @endphp
            <option value="{{ $a->id }}"
                    @selected(optional($studentPending)->adviser_id === $a->id)
                    data-meta='@json($meta)'>
              {{ $a->name }}@if($p?->department) — {{ $p->department }} @endif
            </option>
          @endforeach
        </select>

        <form id="changeForm-{{ $t->id }}" method="POST" action="{{ route('titles.adviser.change', $t) }}">
          @csrf
          <input type="hidden" name="adviser_id" id="adviser_id_hidden_{{ $t->id }}">
          <button type="submit"
                  class="shrink-0 px-2.5 py-1 rounded-md bg-indigo-600 text-white text-xs hover:bg-indigo-700">
            Change
          </button>
        </form>
      </div>
    @endif
  </div>
</div>

  </div>

  {{-- FULL-WIDTH ADVISER INFO (spans the entire card) --}}
  <div id="adv-card-{{ $t->id }}" class="hidden mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
    <div class="grid gap-4 md:grid-cols-12">
      {{-- LEFT: avatar + name + dept/field --}}
      <div class="md:col-span-4 lg:col-span-3">
        <div class="flex items-start gap-3">
          <img id="adv-avatar-{{ $t->id }}" class="h-14 w-14 rounded-full object-cover"
               src="{{ asset('storage/avatars/default.png') }}" alt="Adviser avatar">
          <div class="min-w-0">
            <div id="adv-name-{{ $t->id }}" class="text-base font-semibold text-gray-900">—</div>
            <div id="adv-dept-{{ $t->id }}" class="text-sm text-gray-600">—</div>
            <div id="adv-field-{{ $t->id }}" class="text-sm text-gray-600">—</div>
          </div>
        </div>
      </div>

      {{-- RIGHT: stats + interests (fills remaining width) --}}
      <div class="md:col-span-8 lg:col-span-9">
        <div class="grid gap-3 sm:grid-cols-3">
          <div class="rounded-lg bg-white border p-3">
            <div class="text-xs text-gray-500">Highest Degree</div>
            <div id="adv-degree-{{ $t->id }}" class="text-sm font-medium text-gray-800 mt-0.5">—</div>
          </div>
          <div class="rounded-lg bg-white border p-3">
            <div class="text-xs text-gray-500">Advisory Years</div>
            <div id="adv-years-{{ $t->id }}" class="text-sm font-medium text-gray-800 mt-0.5">—</div>
          </div>
          <div class="rounded-lg bg-white border p-3">
            <div class="text-xs text-gray-500">Projects</div>
            <div id="adv-projects-{{ $t->id }}" class="text-sm font-medium text-gray-800 mt-0.5">—</div>
          </div>
        </div>

        <div class="mt-3">
          <div class="text-xs text-gray-500 mb-1">Interests</div>
          <div id="adv-interests-{{ $t->id }}" class="flex flex-wrap gap-1.5">
            <span class="text-[11px] text-gray-400 italic">—</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- incoming adviser-initiated requests (optional compact list) --}}
@if($incomingFromAdvisers->isNotEmpty())
  <div class="mt-3">
    <ul class="space-y-2">
      @foreach($incomingFromAdvisers as $req)
        @php
          // Reuse the same meta shape used in the Change Adviser select
          $advMeta = $buildMeta($req->adviser);
        @endphp
        <li class="border border-gray-200 rounded-md p-3">
          <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <img class="h-8 w-8 rounded-full object-cover"
                     src="{{ $advMeta['avatar'] }}" alt="Adviser avatar">
                <div class="truncate">
                  <span class="text-sm font-semibold text-gray-900">{{ $req->adviser->name }}</span>
                  @if(($advMeta['profile']['department'] ?? null))
                    <span class="text-xs text-gray-500">• {{ $advMeta['profile']['department'] }}</span>
                  @endif
                </div>
              </div>
              @if(($advMeta['profile']['field_of_expertise'] ?? null))
                <div class="text-xs text-gray-600 mt-0.5 line-clamp-1">
                  {{ $advMeta['profile']['field_of_expertise'] }}
                </div>
              @endif
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
              <button type="button"
                      class="px-2.5 py-1 rounded-md border border-gray-300 text-gray-700 text-xs hover:bg-gray-50"
                      data-adv-profile='@json($advMeta)'
                      data-open-adv-profile>
                View profile
              </button>

              <form id="acceptForm-{{ $t->id }}-{{ $req->id }}" method="POST"
                    action="{{ route('titles.incoming.accept', [$t, $req]) }}">
                @csrf
                <button type="button"
                        class="px-2.5 py-1 rounded-md bg-green-600 text-white text-xs hover:bg-green-700"
                        data-confirm
                        data-title="Accept Adviser"
                        data-message="Accept {{ $req->adviser->name }} as adviser for “{{ $t->title }}”?"
                        data-form="acceptForm-{{ $t->id }}-{{ $req->id }}">
                  Accept
                </button>
              </form>

              <form id="declineForm-{{ $t->id }}-{{ $req->id }}" method="POST"
                    action="{{ route('titles.incoming.decline', [$t, $req]) }}">
                @csrf
                <button type="button"
                        class="px-2.5 py-1 rounded-md bg-red-600 text-white text-xs hover:bg-red-700"
                        data-confirm
                        data-title="Decline Adviser"
                        data-message="Decline {{ $req->adviser->name }}’s request for “{{ $t->title }}”?"
                        data-form="declineForm-{{ $t->id }}-{{ $req->id }}">
                  Decline
                </button>
              </form>
            </div>
          </div>
        </li>
      @endforeach
    </ul>
  </div>
@endif

</li>

{{-- per-row script (unchanged logic, works with full-width card) --}}
<script>
  (function(){
    const rowId = "{{ $t->id }}";
    const select = document.getElementById('adviser_id_' + rowId);
    const hidden = document.getElementById('adviser_id_hidden_' + rowId);
    const toggle = document.getElementById('toggle-details-' + rowId);
    const card   = document.getElementById('adv-card-' + rowId);

    function chips(el, arr){
      el.innerHTML = '';
      if (!Array.isArray(arr) || arr.length === 0) {
        const span = document.createElement('span');
        span.className = 'text-[11px] text-gray-400 italic';
        span.textContent = '—';
        el.appendChild(span);
        return;
      }
      arr.forEach(t=>{
        const s = document.createElement('span');
        s.className = 'text-[11px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100';
        s.textContent = t;
        el.appendChild(s);
      });
    }

    function render(meta){
      const p = meta && meta.profile ? meta.profile : null;
      document.getElementById('adv-avatar-' + rowId).src = (meta && meta.avatar) ? meta.avatar : "{{ asset('storage/avatars/default.png') }}";
      document.getElementById('adv-name-' + rowId).textContent  = meta?.name || '—';
      document.getElementById('adv-dept-' + rowId).textContent  = p?.department || (p === null ? 'No profile yet' : '—');
      document.getElementById('adv-field-' + rowId).textContent = p?.field_of_expertise || '—';

      const deg = p ? [p.highest_degree, p.degree_school, p.degree_year].filter(Boolean).join(', ') : '';
      document.getElementById('adv-degree-' + rowId).textContent   = deg || '—';
      document.getElementById('adv-years-' + rowId).textContent    = (p?.advisory_years ?? '') !== '' ? p.advisory_years : '—';
      document.getElementById('adv-projects-' + rowId).textContent = (p?.projects_handled ?? '') !== '' ? p.projects_handled : '—';
      chips(document.getElementById('adv-interests-' + rowId), p?.interests || []);
    }

    function parseMeta(opt){
      try {
        const raw = opt.getAttribute('data-meta');
        return raw ? JSON.parse(raw) : null;
      } catch(e){ return null; }
    }

    // sync select → hidden
    function syncHidden(){ if (hidden) hidden.value = select.value || ''; }
    select.addEventListener('change', ()=>{
      syncHidden();
      const opt = select.options[select.selectedIndex];
      const meta = opt ? parseMeta(opt) : null;
      if (meta) render(meta);
      if (toggle.checked) card.classList.remove('hidden');
    });
    syncHidden(); // initial

    // row toggle
    toggle.addEventListener('change', ()=>{
      if (toggle.checked) {
        const opt = select.options[select.selectedIndex];
        const meta = opt ? parseMeta(opt) : null;
        if (meta) render(meta);
        card.classList.remove('hidden');
      } else {
        card.classList.add('hidden');
      }
    });

    // respect global default (if you enabled that checkbox above)
    try {
      const globalOn = localStorage.getItem('awaiting_show_details_default') === '1';
      if (globalOn) { toggle.checked = true; toggle.dispatchEvent(new Event('change')); }
    } catch(e){}
  })();
</script>





        @endforeach
      </ul>

      <div class="mt-4">
      {{ $titles->appends(request()->query())->links() }}
      </div>
    @endif
  </div>

  {{-- Confirm Modal (unchanged) --}}
  <div id="confirmModal" class="fixed inset-0 hidden items-center justify-center z-50">
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




  {{-- Adviser Profile Modal (global, reusable) --}}
<div id="adviserProfileModal" class="fixed inset-0 hidden items-center justify-center z-[60]">
  <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" data-adv-prof-close></div>
  <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 p-5">
    <div class="flex items-start gap-4">
      <img id="apm-avatar" class="h-16 w-16 rounded-full object-cover"
           src="{{ asset('storage/avatars/default.png') }}" alt="Adviser avatar">
      <div class="min-w-0">
        <div id="apm-name" class="text-lg font-semibold text-gray-900">—</div>
        <div id="apm-dept" class="text-sm text-gray-600">—</div>
        <div id="apm-field" class="text-sm text-gray-600">—</div>
      </div>
      <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" data-adv-prof-close>
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
      <div class="rounded-lg border bg-gray-50 p-3">
        <div class="text-xs text-gray-500">Highest Degree</div>
        <div id="apm-degree" class="text-sm font-medium text-gray-800 mt-0.5">—</div>
      </div>
      <div class="rounded-lg border bg-gray-50 p-3">
        <div class="text-xs text-gray-500">Advisory Years</div>
        <div id="apm-years" class="text-sm font-medium text-gray-800 mt-0.5">—</div>
      </div>
      <div class="rounded-lg border bg-gray-50 p-3">
        <div class="text-xs text-gray-500">Projects</div>
        <div id="apm-projects" class="text-sm font-medium text-gray-800 mt-0.5">—</div>
      </div>
    </div>

    <div class="mt-4">
      <div class="text-xs text-gray-500 mb-1">Interests</div>
      <div id="apm-interests" class="flex flex-wrap gap-1.5">
        <span class="text-[11px] text-gray-400 italic">—</span>
      </div>
    </div>

    <div class="mt-4">
      <div class="text-xs text-gray-500 mb-1">Achievements</div>
      <ul id="apm-achievements" class="space-y-1">
        <li class="text-[11px] text-gray-400 italic">—</li>
      </ul>
    </div>

    <div class="mt-4">
      <div class="text-xs text-gray-500 mb-1">Notes</div>
      <p id="apm-notes" class="text-sm text-gray-700">—</p>
    </div>

    <div class="mt-5 flex justify-end">
      <button type="button"
              class="px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm"
              data-adv-prof-close>
        Close
      </button>
    </div>
  </div>
</div>




<script>
  (function () {
    const modal = document.getElementById('adviserProfileModal');
    const avatar = document.getElementById('apm-avatar');
    const nameEl = document.getElementById('apm-name');
    const deptEl = document.getElementById('apm-dept');
    const fieldEl = document.getElementById('apm-field');
    const degreeEl = document.getElementById('apm-degree');
    const yearsEl = document.getElementById('apm-years');
    const projectsEl = document.getElementById('apm-projects');
    const interestsEl = document.getElementById('apm-interests');
    const achievementsEl = document.getElementById('apm-achievements');
    const notesEl = document.getElementById('apm-notes');

    function chips(el, arr){
      el.innerHTML = '';
      if (!Array.isArray(arr) || arr.length === 0) {
        const s = document.createElement('span');
        s.className = 'text-[11px] text-gray-400 italic';
        s.textContent = '—';
        el.appendChild(s);
        return;
      }
      arr.forEach(t => {
        const s = document.createElement('span');
        s.className = 'text-[11px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100';
        s.textContent = t;
        el.appendChild(s);
      });
    }

    function listAchievements(el, arr){
      el.innerHTML = '';
      if (!Array.isArray(arr) || arr.length === 0) {
        const li = document.createElement('li');
        li.className = 'text-[11px] text-gray-400 italic';
        li.textContent = '—';
        el.appendChild(li);
        return;
      }
      arr.forEach(a => {
        const li = document.createElement('li');
        li.className = 'text-sm text-gray-800';
        const parts = [a.title, a.issuer, a.year].filter(Boolean).join(' • ');
        li.textContent = parts || a.title || 'Achievement';
        el.appendChild(li);
      });
    }

    function openModal(meta){
      const p = meta && meta.profile ? meta.profile : null;

      avatar.src = (meta && meta.avatar) ? meta.avatar : "{{ asset('storage/avatars/default.png') }}";
      nameEl.textContent = meta?.name || '—';
      deptEl.textContent = p?.department || (p === null ? 'No profile yet' : '—');
      fieldEl.textContent = p?.field_of_expertise || '—';

      const deg = p ? [p.highest_degree, p.degree_school, p.degree_year].filter(Boolean).join(', ') : '';
      degreeEl.textContent = deg || '—';
      yearsEl.textContent = (p?.advisory_years ?? '') !== '' ? p.advisory_years : '—';
      projectsEl.textContent = (p?.projects_handled ?? '') !== '' ? p.projects_handled : '—';

      chips(interestsEl, p?.interests || []);
      listAchievements(achievementsEl, p?.achievements || []);

      notesEl.textContent = p?.notes || '—';

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal(){
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    document.addEventListener('click', (e) => {
      // Open profile
      const btn = e.target.closest('[data-open-adv-profile]');
      if (btn) {
        try {
          const meta = JSON.parse(btn.getAttribute('data-adv-profile') || '{}');
          openModal(meta);
        } catch(err) {
          // fail silently
        }
        return;
      }
      // Close profile
      if (e.target.closest('[data-adv-prof-close]')) {
        closeModal();
      }
    });

    // ESC to close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeModal();
    });
  })();
</script>



  <script>
    // Confirm modal (unchanged)
    (function () {
      const modal   = document.getElementById('confirmModal');
      const overlay = document.getElementById('confirmOverlay');
      const titleEl = document.getElementById('confirmTitle');
      const msgEl   = document.getElementById('confirmMessage');
      const okBtn   = document.getElementById('confirmOkBtn');
      const cancel  = document.getElementById('confirmCancelBtn');
      let targetFormId = null;

      function openModal({ title, message, formId }) {
        titleEl.textContent = title || 'Confirm';
        msgEl.textContent   = message || 'Are you sure?';
        targetFormId        = formId || null;
        modal.classList.remove('hidden'); modal.classList.add('flex');
      }
      function closeModal() {
        modal.classList.add('hidden'); modal.classList.remove('flex');
        titleEl.textContent = 'Confirm'; msgEl.textContent = 'Are you sure?'; targetFormId = null;
      }
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-confirm]'); if (!btn) return;
        openModal({
          title: btn.getAttribute('data-title'),
          message: btn.getAttribute('data-message'),
          formId: btn.getAttribute('data-form')
        });
      });
      overlay.addEventListener('click', closeModal);
      cancel.addEventListener('click', closeModal);
      okBtn.addEventListener('click', () => {
        if (targetFormId) { const f = document.getElementById(targetFormId); if (f) f.submit(); }
        closeModal();
      });
    })();

    // Global default for adviser detail visibility (localStorage)
    (function(){
      const box = document.getElementById('toggle-global-details');
      if (!box) return;
      try { box.checked = localStorage.getItem('awaiting_show_details_default') === '1'; } catch(e){}
      box.addEventListener('change', ()=>{
        try { localStorage.setItem('awaiting_show_details_default', box.checked ? '1' : '0'); } catch(e){}
        // Tip: we do not auto-open all rows to avoid heavy DOM churn; each row reads this on init.
      });
    })();
  </script>
</x-userlayout>
