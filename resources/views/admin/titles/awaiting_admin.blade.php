{{-- resources/views/admin/titles/awaiting_admin.blade.php --}}
<x-userlayout>

 

    <div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Waiting For Approval</h2>
    </div>
   
  </div>
</div>

    {{-- Filters / Search --}}
    <form method="GET" action="{{ route('admin.titles.awaiting') }}" class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                placeholder="Search by title or student name...">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Per page</label>
            <select name="per_page"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @foreach([5,10,15,25,50] as $n)
                    <option value="{{ $n }}" @selected(request('per_page', 10)==$n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit"
                class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                Apply
            </button>
        </div>
    </form>

    {{-- Table --}}
    <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                @if($titles->total() > 0)
                    Showing <span class="font-semibold">{{ $titles->firstItem() }}</span>–
                    <span class="font-semibold">{{ $titles->lastItem() }}</span> of
                    <span class="font-semibold">{{ $titles->total() }}</span>
                @else
                    No records found.
                @endif
            </div>
            <span class="inline-flex items-center text-xs font-semibold px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                Waiting for: Admin approval
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Student</th>
                        <th class="px-6 py-3">Adviser</th>
                        <th class="px-6 py-3">Assigned</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($titles as $t)
                        @php
                            $adv = $t->adviser;
                            $p   = $adv?->adviserProfile;
                            // Build compact metadata (mirrors the student-side card)
                            $meta = $adv ? [
                                'id'     => $adv->id,
                                'name'   => $adv->name,
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
                            ] : null;
                        @endphp

                        <tr>
                            {{-- Title --}}
                            <td class="px-6 py-4 align-top">
                                <div class="font-semibold text-gray-900">{{ $t->title }}</div>
                                @if($t->abstract)
                                    <div class="text-gray-500 text-xs mt-1 line-clamp-2">{{ Str::limit(strip_tags($t->abstract), 150) }}</div>
                                @endif
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        awaiting_admin
                                    </span>
                                    @if($t->finalDocument)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                            has combined doc
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Student --}}
                            <td class="px-6 py-4 align-top">
                                <div class="text-gray-900 font-medium">{{ $t->owner->name ?? '—' }}</div>
                                <div class="text-gray-500 text-xs">{{ $t->owner->email ?? '' }}</div>
                            </td>

                            {{-- Adviser + “View profile” --}}
                            <td class="px-6 py-4 align-top">
                                @if($adv)
                                    <div class="flex items-start gap-3">
                                        <img class="h-9 w-9 rounded-full object-cover"
                                             src="{{ $adv->avatar ? asset('storage/avatars/'.$adv->avatar) : asset('storage/avatars/default.png') }}"
                                             alt="avatar">
                                        <div>
                                            <div class="text-gray-900 font-medium">{{ $adv->name }}</div>
                                            @if($p?->department || $p?->field_of_expertise)
                                                <div class="text-gray-500 text-xs">
                                                    {{ $p?->department ?? '—' }}
                                                    @if($p?->field_of_expertise)
                                                        • {{ $p->field_of_expertise }}
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Trigger button carries JSON metadata --}}
                                            <button type="button"
                                                class="mt-1 inline-flex items-center gap-1 text-xs text-blue-700 hover:underline"
                                                data-advmeta='@json($meta)'
                                                data-action="open-adv-modal">
                                                {{-- icon --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View profile
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-gray-400">—</div>
                                @endif
                            </td>

                            {{-- Assigned --}}
                            <td class="px-6 py-4 align-top">
                                <div class="text-gray-900">
                                    {{ $t->adviser_assigned_at ? $t->adviser_assigned_at->format('M d, Y') : '—' }}
                                </div>
                                @if($t->adviser_assigned_at)
                                    <div class="text-gray-500 text-xs">{{ $t->adviser_assigned_at->diffForHumans() }}</div>
                                @endif
                            </td>

                 
                           {{-- Actions --}}
                        <td class="px-6 py-4 align-top">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Approve --}}
                            <form method="POST" action="{{ route('admin.titles.approve', $t) }}"
                                onsubmit="return confirm('Approve this adviser assignment and unlock editing for the student?');">
                            @csrf
                           {{-- Approve (opens modal) --}}
<button type="button"
    class="inline-flex items-center px-3 py-1.5 rounded-md bg-green-600 text-white text-xs font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 whitespace-nowrap"
    data-open-approve
    data-url="{{ route('admin.titles.approve', $t) }}"
    data-title="{{ $t->title }}">
    Approve
</button>

                            </form>

                            {{-- Return (opens modal) --}}
                            <button type="button"
                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-600 text-white text-xs font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 whitespace-nowrap"
                            data-open-return
                            data-url="{{ route('admin.titles.return', $t) }}"
                            data-title="{{ $t->title }}">
                            Return
                            </button>
                        </div>
                        </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Nothing to approve right now.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($titles->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $titles->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Adviser Profile Modal (single instance, reused) ===== --}}
    <div id="admin-adv-modal" class="fixed inset-0 hidden z-50">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-lg">
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Adviser Profile</h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700" data-action="close-adv-modal">
                        ✕
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div class="flex items-start gap-4">
                        <img id="advm-avatar" class="h-14 w-14 rounded-full object-cover shadow-sm"
                             src="{{ asset('storage/avatars/default.png') }}" alt="avatar">
                        <div class="min-w-0">
                            <div id="advm-name" class="text-lg font-semibold text-gray-900">—</div>
                            <div id="advm-dept" class="text-sm text-gray-600">—</div>
                            <div id="advm-field" class="text-sm text-gray-600">—</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-lg bg-gray-50 border p-3">
                            <div class="text-xs text-gray-500">Highest Degree</div>
                            <div id="advm-degree" class="text-sm font-medium text-gray-800">—</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 border p-3">
                            <div class="text-xs text-gray-500">Advisory Years</div>
                            <div id="advm-years" class="text-sm font-medium text-gray-800">—</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 border p-3">
                            <div class="text-xs text-gray-500">Projects Handled</div>
                            <div id="advm-projects" class="text-sm font-medium text-gray-800">—</div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 border p-3">
                        <div class="text-xs text-gray-500 mb-1">Research Interests</div>
                        <div id="advm-interests" class="flex flex-wrap gap-2">
                            <span class="text-xs text-gray-400 italic">—</span>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 border p-3">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-800">Major Achievements</div>
                            <button type="button" class="text-xs text-blue-600 hover:underline" id="advm-toggle-ach" aria-expanded="true">
                                Collapse
                            </button>
                        </div>
                        <ul id="advm-achievements" class="mt-2 space-y-2">
                            <li class="text-sm text-gray-500 italic">—</li>
                        </ul>
                    </div>

                    <div class="rounded-lg bg-gray-50 border p-3">
                        <div class="text-xs text-gray-500 mb-1">Notes / Bio</div>
                        <p id="advm-notes" class="text-sm text-gray-700">—</p>
                    </div>
                </div>

                <div class="px-5 py-3 border-t flex justify-end">
                    <button type="button" class="px-4 py-2 rounded-md border text-gray-700 hover:bg-gray-50"
                            data-action="close-adv-modal">Close</button>
                </div>
            </div>
        </div>
    </div>




    {{-- ===== Return Modal (reused for all rows) ===== --}}
<div id="return-modal" class="fixed inset-0 hidden z-50">
  <div class="absolute inset-0 bg-black/40" data-close-return></div>

  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-lg">
      <div class="px-5 pt-2 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900">
          Send Back to Student
        </h3>
        <button type="button" class="text-gray-500 hover:text-gray-700" data-close-return>✕</button>
      </div>

      <form id="return-form" method="POST" action="#">
        @csrf
        <div class="p-5 space-y-2">
          <div class="rounded-md bg-yellow-50 border border-yellow-200 text-yellow-900 text-xs px-3 py-2">
            <span class="font-semibold">Waiting for: Admin approval</span> • You can include a note before sending back.
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Reason (optional)
            </label>
            <textarea name="reason" rows="4"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500"
              placeholder="What needs to be revised?"></textarea>
          </div>

          <div class="text-xs text-gray-500" id="return-context">Title: —</div>
        </div>

        <div class="px-5 py-3 border-t border-gray-200 flex justify-end gap-2">
          <button type="button" class="px-3 py-1.5 text-xs rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50" data-close-return>
            Cancel
          </button>
          <button type="submit" class="px-3 py-1.5 text-xs rounded-md bg-red-600 text-white font-semibold hover:bg-red-700">
            Send Back
          </button>
        </div>
      </form>
    </div>
  </div>
</div>




{{-- ===== Approve Modal (reused for all rows) ===== --}}
<div id="approve-modal" class="fixed inset-0 hidden z-50">
  <div class="absolute inset-0 bg-black/40" data-close-approve></div>

  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-lg">
      <div class="px-5 pt-2 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900">
          Approve Adviser Assignment
        </h3>
        <button type="button" class="text-gray-500 hover:text-gray-700" data-close-approve>✕</button>
      </div>

      <form id="approve-form" method="POST" action="#">
        @csrf
        <div class="p-5 space-y-3">
          <div class="rounded-md bg-green-50 border border-green-200 text-green-900 text-xs px-3 py-2">
            <span class="font-semibold">Action:</span> Approve this adviser assignment and unlock editing for the student.
          </div>

          <div class="text-sm text-gray-700">
            Are you sure you want to approve <span class="font-semibold">this title</span>?
          </div>
          <div class="text-xs text-gray-500" id="approve-context">Title: —</div>
        </div>

        <div class="px-5 py-3 border-t border-gray-200 flex justify-end gap-2">
          <button type="button" class="px-3 py-1.5 text-xs rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50" data-close-approve>
            Cancel
          </button>
          <button type="submit" class="px-3 py-1.5 text-xs rounded-md bg-green-600 text-white font-semibold hover:bg-green-700">
            Confirm Approve
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
(function () {
  const modal = document.getElementById('approve-modal');
  const form  = document.getElementById('approve-form');
  const ctx   = document.getElementById('approve-context');

  function openApprove(url, title) {
    form.setAttribute('action', url);
    ctx.textContent = 'Title: ' + (title || '—');
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
  function closeApprove() {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    // no inputs to reset, but keep symmetrical with return modal
  }

  document.addEventListener('click', (e) => {
    const openBtn = e.target.closest('[data-open-approve]');
    if (openBtn) {
      openApprove(openBtn.dataset.url, openBtn.dataset.title);
    }
    if (e.target.matches('[data-close-approve]')) {
      closeApprove();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeApprove();
  });
})();
</script>


<script>
(function () {
  const modal   = document.getElementById('return-modal');
  const form    = document.getElementById('return-form');
  const ctx     = document.getElementById('return-context');

  function openReturn(url, title) {
    form.setAttribute('action', url);
    ctx.textContent = 'Title: ' + (title || '—');
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
  function closeReturn() {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    form.reset();
  }

  document.addEventListener('click', (e) => {
    // open buttons
    const openBtn = e.target.closest('[data-open-return]');
    if (openBtn) {
      openReturn(openBtn.dataset.url, openBtn.dataset.title);
    }
    // close (overlay or buttons)
    if (e.target.matches('[data-close-return]')) {
      closeReturn();
    }
  });

  // ESC closes
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeReturn();
  });
})();
</script>



    {{-- ===== Modal JS (event delegation; no duplicate IDs per row) ===== --}}
    <script>
    (function(){
        const modal = document.getElementById('admin-adv-modal');
        const $ = (id) => document.getElementById(id);

        const avatar   = $('advm-avatar');
        const nameEl   = $('advm-name');
        const deptEl   = $('advm-dept');
        const fieldEl  = $('advm-field');
        const degreeEl = $('advm-degree');
        const yearsEl  = $('advm-years');
        const projEl   = $('advm-projects');
        const intWrap  = $('advm-interests');
        const achList  = $('advm-achievements');
        const notesEl  = $('advm-notes');
        const toggle   = $('advm-toggle-ach');

        function esc(s){ return (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }
        function chips(el, list){
            el.innerHTML = '';
            if (!Array.isArray(list) || list.length === 0){
                const span = document.createElement('span');
                span.className = 'text-xs text-gray-400 italic';
                span.textContent = '—';
                el.appendChild(span);
                return;
            }
            list.forEach(t=>{
                const chip = document.createElement('span');
                chip.className = 'text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100';
                chip.textContent = t;
                el.appendChild(chip);
            });
        }
        function achievements(el, list){
            el.innerHTML = '';
            if (!Array.isArray(list) || list.length === 0){
                const li = document.createElement('li');
                li.className = 'text-sm text-gray-500 italic';
                li.textContent = '—';
                el.appendChild(li);
                return;
            }
            list.forEach(a=>{
                const li = document.createElement('li');
                li.className = 'text-sm text-gray-800';
                const bits = [];
                if (a.title) bits.push(`<span class="font-medium">${esc(a.title)}</span>`);
                const tail = [a.issuer, a.year].filter(Boolean).join(' • ');
                if (tail) bits.push(`<span class="text-gray-500">(${esc(tail)})</span>`);
                if (a.description) bits.push(`<div class="text-gray-600">${esc(a.description)}</div>`);
                li.innerHTML = bits.join(' ');
                el.appendChild(li);
            });
        }
        function fill(meta){
            const p = meta && meta.profile ? meta.profile : null;
            avatar.src = meta?.avatar || "{{ asset('storage/avatars/default.png') }}";
            nameEl.textContent  = meta?.name || '—';
            deptEl.textContent  = p?.department || (p===null ? 'No profile yet' : '—');
            fieldEl.textContent = p?.field_of_expertise || '—';

            if (p){
                const deg = [p.highest_degree, p.degree_school, p.degree_year].filter(Boolean).join(', ');
                degreeEl.textContent = deg || '—';
                yearsEl.textContent  = (p.advisory_years ?? '') !== '' ? p.advisory_years : '—';
                projEl.textContent   = (p.projects_handled ?? '') !== '' ? p.projects_handled : '—';
                notesEl.textContent  = p.notes || '—';
                chips(intWrap, p.interests || []);
                achievements(achList, p.achievements || []);
            } else {
                degreeEl.textContent = yearsEl.textContent = projEl.textContent = '—';
                notesEl.textContent  = '—';
                chips(intWrap, []);
                achievements(achList, []);
            }
        }
        function open(meta){
            fill(meta || {});
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function close(){
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Delegated click: open/close
        document.addEventListener('click', (e)=>{
            const btnOpen = e.target.closest('[data-action="open-adv-modal"]');
            const btnClose = e.target.closest('[data-action="close-adv-modal"]');
            if (btnOpen){
                const raw = btnOpen.getAttribute('data-advmeta');
                try { open(JSON.parse(raw)); } catch(_){ open(null); }
            }
            if (btnClose || e.target === modal.querySelector('.bg-black\\/40')) close();
        });

        // Toggle achievements
        toggle?.addEventListener('click', ()=>{
            const hidden = achList.classList.toggle('hidden');
            toggle.setAttribute('aria-expanded', String(!hidden));
            toggle.textContent = hidden ? 'Expand' : 'Collapse';
        });
    })();
    </script>

</x-userlayout>
