<x-userlayout>
 

<x-header.bar
  title="Title Verification"
  subtitle="Ensure research title uniqueness and quality standards"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

  <div class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 bg-white mt-5 sm:mt-7 shadow rounded-xl">
    <form method="POST" action="{{ route('titles.verify.submit') }}">
      @csrf

      <div class="mb-5 sm:mb-6">
        <label for="title" class="block mb-2 font-bold text-blue-600 text-sm sm:text-base">Document Title</label>
        <input 
          type="text" 
          name="title" 
          id="title"
          class="w-full border border-gray-300 rounded-md px-3 sm:px-4 py-2.5 sm:py-3 text-base sm:text-lg focus:outline-none focus:ring-1 focus:ring-blue-400"
          placeholder="Enter title to verify"
          required
        >
      </div>


      <!-- NEW: Title Description -->
<!-- NEW: Title Description (shown only AFTER verification passes) -->
<div id="description-box" class="mb-5 sm:mb-6 hidden">
  <label for="description" class="block mb-2 font-bold text-blue-600 text-sm sm:text-base">
    Title Description
  </label>
  <textarea
    name="description"
    id="description"
    rows="4"
    class="w-full border border-gray-300 rounded-md px-3 sm:px-4 py-2.5 sm:py-3 text-base sm:text-lg focus:outline-none focus:ring-1 focus:ring-blue-400"
    placeholder="Briefly describe what the research is about (scope, population/context, method, expected contribution)"
    disabled
  ></textarea>
  <p class="text-xs text-gray-500 mt-1">
    Provide a concise summary (e.g., 2–4 sentences) so advisers can quickly understand your study.
  </p>
</div>



      <!-- Buttons Row -->
      <div class="mb-4 sm:mb-3 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
        <!-- Verify -->
        <button type="button"
                onclick="startVerification(event)"
                id="verify-btn"
                class="w-full sm:w-auto px-4 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 active:scale-[.99]">
          Verify Title
        </button>

        <!-- NEW: AI Feedback Toggle (disabled/hidden until relevant) -->
      <button type="button"
        id="ai-toggle-btn"
        class="w-full sm:w-auto px-3 py-2 shadow text-blue-700 rounded-lg hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed hidden"
        aria-controls="ai-feedback"
        aria-expanded="false"
        onclick="toggleAIPanel()">
  <span class="inline-flex items-center gap-2">
    <!-- AI/bot icon -->
    <svg xmlns="http://www.w3.org/2000/svg" 
         class="w-4 h-4" 
         fill="none" 
         viewBox="0 0 24 24" 
         stroke="currentColor" 
         stroke-width="2">
      <rect x="9" y="9" width="6" height="6" rx="1" />
      <path d="M3 9h2M3 15h2M19 9h2M19 15h2M9 3v2M15 3v2M9 19v2M15 19v2" />
    </svg>
    <span id="ai-toggle-label">AI Feedback</span>
  </span>
</button>



        <span id="reject-hint" class="text-sm text-rose-600 hidden sm:ml-2">Title Rejected</span>
      </div>



<!-- AI Feedback (minimal, compact, neutral palette) -->
<div id="ai-feedback" class="mt-4 hidden">
  <section class="rounded-lg border border-slate-200 bg-slate-50/70 p-3 sm:p-4">
    <!-- Header -->
    <header class="flex items-center gap-2 mb-3">
      <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-white text-[10px] font-medium">AI</span>
      <h3 id="ai-heading" class="font-semibold text-slate-800 text-sm sm:text-base tracking-tight">
        Why it was rejected
      </h3>
    </header>

    <!-- Content -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Reasons -->
      <div class="rounded-md border border-slate-200/80 bg-white p-3">
        <h4 class="font-medium text-slate-800 text-sm mb-2">Reason for Rejection</h4>
        <ul id="ai-reasons" class="text-slate-700 text-[13px] leading-relaxed space-y-1 list-disc list-inside">
          <!-- filled by JS -->
        </ul>
      </div>

      <!-- Tips -->
      <div class="rounded-md border border-slate-200/80 bg-white p-3">
        <h4 class="font-medium text-slate-800 text-sm mb-2">Tips to Improve</h4>
        <ul id="ai-suggestions" class="text-slate-700 text-[13px] leading-relaxed space-y-1 list-disc list-inside">
          <!-- filled by JS -->
        </ul>
      </div>
    </div>

    <!-- Reminder -->
    <div class="mt-3 rounded-md border border-slate-200 bg-white p-3">
      <p id="ai-reminder" class="text-[13px] text-slate-700">
        ✅ <span class="font-medium">Reminder:</span>
        Our system is designed to ensure that every research is unique and original.
        Refine your title to highlight <em>new perspectives, specific contexts, or unique approaches</em>.
      </p>
    </div>
  </section>
</div>




      <!-- Similarity Result Bars -->
      <div class="flex flex-col xl:flex-row items-stretch gap-3 sm:gap-4 mt-3 sm:mt-5 mb-2 min-w-0">
        <!-- Internal -->
        <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
          <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
            <span class="font-bold text-blue-700">Internal Similar Titles:</span>
            <span id="similarity-result" class="truncate">Waiting for verification...</span>
          </div>
          <div class="flex-1 bg-gray-200 rounded-full h-3">
            <div id="similarity-bar" class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
          </div>
          <span id="similarity-percent" class="text-[11px] sm:text-xs text-gray-500 whitespace-nowrap">0%</span>
        </div>

        <!-- Web -->
        <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
          <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
            <span class="font-bold text-blue-700">Web Similar Titles:</span>
            <span id="external-similarity-result" class="truncate">Waiting for verification...</span>
          </div>
          <div class="flex-1 bg-gray-200 rounded-full h-3">
            <div id="external-similarity-bar" class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
          </div>
          <span id="external-similarity-percent" class="text-[11px] sm:text-xs text-gray-500 whitespace-nowrap">0%</span>
        </div>
      </div>

      <!-- Similar Titles Lists -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="rounded-md border border-gray-200">
          <ul id="internal-similar-titles" 
              class="list-inside list-disc space-y-1 text-sm text-gray-700 bg-gray-50 p-3 sm:p-4 min-h-[160px] max-h-[45vh] lg:max-h-[28rem] overflow-y-auto">
            <li class="italic text-gray-400">No similar internal titles found.</li>
          </ul>
        </div>
        <div class="rounded-md border border-gray-200">
          <ul id="web-similar-titles" 
              class="list-inside list-disc space-y-1 text-sm text-gray-700 bg-gray-50 p-3 sm:p-4 min-h-[160px] max-h-[45vh] lg:max-h-[28rem] overflow-y-auto">
            <li class="italic text-gray-400">No similar web titles found.</li>
          </ul>
        </div>
      </div>








<!-- Adviser Decision (shown only after both checks pass) -->
<div id="adviser-decision" class="mt-6 hidden">
  <label class="block mb-2 font-bold text-blue-600 text-sm sm:text-base">Adviser Decision</label>

  <div class="flex flex-col sm:flex-row gap-2">
    <label class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer hover:bg-blue-50">
      <input type="radio" name="adviser_mode" id="adviser_mode_with" value="with" class="accent-blue-600" checked>
      <span class="text-sm text-gray-800">I have a preferred adviser now</span>
    </label>

    <label class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer hover:bg-blue-50">
      <input type="radio" name="adviser_mode" id="adviser_mode_later" value="later" class="accent-blue-600">
      <span class="text-sm text-gray-800">I’ll choose later</span>
    </label>
  </div>

  <p class="text-xs text-gray-500 mt-2">
    You can request an adviser anytime from your <em>Awaiting Titles</em> page.
  </p>
</div>





      {{-- Adviser chooser (hidden until internal+web pass) --}}
 <div id="adviser-box" class="mt-6 hidden">
  <label for="adviser_id" class="block mb-2 font-bold text-blue-600 text-sm sm:text-base">Choose Adviser</label>

  @if(($advisers ?? collect())->count())
    <select id="adviser_id" name="adviser_id"
            class="w-full border border-gray-300 rounded-md px-3 sm:px-4 py-2.5 sm:py-3 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
            disabled>
      <option value="">— Select an adviser —</option>
      @foreach($advisers as $adv)
        @php
          $p = $adv->adviserProfile;
          $meta = [
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
        @endphp
        <option value="{{ $adv->id }}" data-meta='@json($meta)'>
          {{ $adv->name }}
          @if($p?->department) — {{ $p->department }} @endif
          @if($p?->field_of_expertise) ({{ $p->field_of_expertise }}) @endif
        </option>
      @endforeach
    </select>

    {{-- Adviser Info Card --}}
    <div id="adviser-info" class="hidden mt-4 rounded-xl border border-gray-200 bg-gray-50">
      <div class="p-4 sm:p-5">
        <div class="flex items-start gap-4">
          <img id="adv-avatar" class="h-14 w-14 rounded-full object-cover shadow-sm"
               src="{{ asset('storage/avatars/default.png') }}" alt="Adviser avatar">
          <div class="min-w-0 flex-1">
            <h4 id="adv-name" class="text-lg font-semibold text-gray-900">—</h4>
            <p id="adv-dept" class="text-sm text-gray-600">—</p>
            <p id="adv-field" class="text-sm text-gray-600">—</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
          <div class="rounded-lg bg-white border p-3">
            <div class="text-xs text-gray-500">Highest Degree</div>
            <div id="adv-degree" class="text-sm font-medium text-gray-800">—</div>
          </div>
          <div class="rounded-lg bg-white border p-3">
            <div class="text-xs text-gray-500">Advisory Years</div>
            <div id="adv-years" class="text-sm font-medium text-gray-800">—</div>
          </div>
          <div class="rounded-lg bg-white border p-3">
            <div class="text-xs text-gray-500">Projects Handled</div>
            <div id="adv-projects" class="text-sm font-medium text-gray-800">—</div>
          </div>
        </div>

        <div class="mt-4 rounded-lg bg-white border p-3">
          <div class="text-xs text-gray-500 mb-1">Research Interests</div>
          <div id="adv-interests" class="flex flex-wrap gap-2">
            <span class="text-xs text-gray-400 italic">—</span>
          </div>
        </div>

        <div class="mt-4 rounded-lg bg-white border p-3">
          <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-800">Major Achievements</div>
            <button type="button" id="toggle-achievements"
                    class="text-xs text-blue-600 hover:underline" aria-expanded="true">
              Collapse
            </button>
          </div>
          <ul id="adv-achievements" class="mt-2 space-y-2">
            <li class="text-sm text-gray-500 italic">—</li>
          </ul>
        </div>

        <div class="mt-4 rounded-lg bg-white border p-3">
          <div class="text-xs text-gray-500 mb-1">Notes / Bio</div>
          <p id="adv-notes" class="text-sm text-gray-700">—</p>
        </div>
      </div>
    </div>

    <p class="text-xs text-gray-500 mt-2">
      The adviser will receive your request and can accept/decline.
    </p>
  @else
    <div class="p-3 rounded-md bg-amber-50 border border-amber-200 text-sm text-amber-800">
      No advisers available yet. Please contact the administrator.
    </div>
  @endif
</div>

      <div id="authors-box" class="mt-4 hidden">
        <label for="authors" class="block mb-2 font-bold text-blue-600 text-sm sm:text-base">Authors</label>
        <input 
          type="text" 
          name="authors" 
          id="authors"
          class="w-full border border-gray-300 rounded-md px-3 sm:px-4 py-2.5 sm:py-3 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
          placeholder="Enter author name(s), e.g., Last, First; Last, First"
          disabled
        >
        <p class="text-xs text-gray-500 mt-1">
          Separate multiple authors with commas or semicolons.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row sm:justify-end gap-3 sm:gap-0 mt-5">
        <button id="proceed-btn" type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>
          Proceed to Document
        </button>
      </div>
    </form>
  </div>

  <!-- Loading Overlay -->
  <div id="loading-overlay" class="fixed inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center z-50 hidden">
    <div class="text-center px-6">
      <svg class="animate-spin h-10 w-10 text-blue-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
      <p class="text-blue-700 font-semibold text-base sm:text-lg">Scanning title for similarity...</p>
    </div>
  </div>







{{-- ========== ADVISER METADATA CODE START============= --}}
{{-- ======== ADVISER METADATA (single source of truth) ======== --}}


<script>
(function(){
  const select = document.getElementById('adviser_id');
  const card   = document.getElementById('adviser-info');

  const $ = (id) => document.getElementById(id);
  const avatar  = $('adv-avatar');
  const nameEl  = $('adv-name');
  const deptEl  = $('adv-dept');
  const fieldEl = $('adv-field');
  const degree  = $('adv-degree');
  const years   = $('adv-years');
  const projects= $('adv-projects');
  const interestsWrap = $('adv-interests');
  const achList = $('adv-achievements');
  const notes   = $('adv-notes');
  const toggle  = $('toggle-achievements');

  function esc(s){ return (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }

  function renderInterests(list){
    interestsWrap.innerHTML = '';
    if (!Array.isArray(list) || list.length === 0){
      const span = document.createElement('span');
      span.className = 'text-xs text-gray-400 italic';
      span.textContent = '—';
      interestsWrap.appendChild(span);
      return;
    }
    list.forEach(t=>{
      const chip = document.createElement('span');
      chip.className = 'text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100';
      chip.textContent = t;
      interestsWrap.appendChild(chip);
    });
  }

  function renderAchievements(list){
    achList.innerHTML = '';
    if (!Array.isArray(list) || list.length === 0){
      const li = document.createElement('li');
      li.className = 'text-sm text-gray-500 italic';
      li.textContent = '—';
      achList.appendChild(li);
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
      achList.appendChild(li);
    });
  }

  function fillCard(meta){
    const p = meta && meta.profile ? meta.profile : null;

    card.classList.remove('hidden');
    avatar.src       = (meta && meta.avatar) ? meta.avatar : "{{ asset('storage/avatars/default.png') }}";
    nameEl.textContent  = meta?.name || '—';
    deptEl.textContent  = p?.department || (p === null ? 'No profile yet' : '—');
    fieldEl.textContent = p?.field_of_expertise || '—';

    if (p){
      const deg = [p.highest_degree, p.degree_school, p.degree_year].filter(Boolean).join(', ');
      degree.textContent   = deg || '—';
      years.textContent    = (p.advisory_years ?? '') !== '' ? p.advisory_years : '—';
      projects.textContent = (p.projects_handled ?? '') !== '' ? p.projects_handled : '—';
      notes.textContent    = p.notes || '—';
      renderInterests(p.interests || []);
      renderAchievements(p.achievements || []);
    } else {
      degree.textContent   = '—';
      years.textContent    = '—';
      projects.textContent = '—';
      notes.textContent    = '—';
      renderInterests([]);
      renderAchievements([]);
    }
  }

  function parseMeta(opt){
    try {
      const raw = opt.getAttribute('data-meta');
      if (!raw) return null;
      return JSON.parse(raw);
    } catch(e){ return null; }
  }

  function onChange(){
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) { card.classList.add('hidden'); return; }
    const meta = parseMeta(opt);
    if (meta) fillCard(meta); else card.classList.add('hidden');
  }

  select?.addEventListener('change', onChange);

  // collapse/expand achievements
  toggle?.addEventListener('click', () => {
    const hidden = achList.classList.toggle('hidden');
    toggle.setAttribute('aria-expanded', String(!hidden));
    toggle.textContent = hidden ? 'Expand' : 'Collapse';
  });
})();
</script>

{{-- ========== ADVISER METADATA CODE END============= --}}










  {{-- ===== JS ===== --}}
  <script>
/* ---------- Thresholds (edit here only) ---------- */
const INTERNAL_THRESHOLD = 20; // pass if max internal similarity < 20%
const EXTERNAL_THRESHOLD = 60; // pass if max web similarity < 60%

/* ---------- Helpers ---------- */
function escapeHtml(str){
  return (str || '').replace(/[&<>"']/g, m => (
    {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]
  ));
}
function toggleRejectHint(on){
  const hint = document.getElementById('reject-hint');
  if (on) hint.classList.remove('hidden'); else hint.classList.add('hidden');
}
function updateBars(target, percent, approved, labelWhenWaiting = null) {
  target.bar.style.width = percent + '%';
  target.percent.innerText = percent + '%';
  target.result.innerText = labelWhenWaiting ?? `Similarity: ${percent}% — ${approved ? 'Approved.' : 'Rejected.'}`;
  target.result.classList.toggle('text-green-600', approved);
  target.result.classList.toggle('text-red-600', !approved);
}
function showLoading(message = 'Scanning title for similarity...') {
  document.getElementById('loading-overlay').classList.remove('hidden');
  document.querySelector('#loading-overlay p').textContent = message;
}
function hideLoading() {
  document.getElementById('loading-overlay').classList.add('hidden');
}
function showDecisionBlock(show){
  const dec = document.getElementById('adviser-decision');
  if (!dec) return;
  if (show) dec.classList.remove('hidden'); else dec.classList.add('hidden');
}

function showDescriptionField(show){
  const box = document.getElementById('description-box');
  const desc = document.getElementById('description');
  if (!box || !desc) return;

  if (show) {
    box.classList.remove('hidden');
    desc.disabled = false;
    desc.setAttribute('required','required');
  } else {
    box.classList.add('hidden');
    desc.disabled = true;
    desc.removeAttribute('required');
    // Optional: clear value if you want
    // desc.value = '';
  }
}


function showSectionsForMode(mode){
  const advBox = document.getElementById('adviser-box');
  const advSel = document.getElementById('adviser_id');
  const authBox = document.getElementById('authors-box');
  const authInp = document.getElementById('authors');

  // Authors are always required
  authBox?.classList.remove('hidden');
  if (authInp) { authInp.disabled = false; authInp.setAttribute('required','required'); }

  if (mode === 'with') {
    // Adviser required
    advBox?.classList.remove('hidden');
    if (advSel) { advSel.disabled = false; advSel.setAttribute('required','required'); }
  } else {
    // Adviser hidden
    advBox?.classList.add('hidden');
    if (advSel) { advSel.disabled = true; advSel.removeAttribute('required'); advSel.value = ''; }
  }
}

function getSelectedMode(){
  const withEl  = document.getElementById('adviser_mode_with');
  const laterEl = document.getElementById('adviser_mode_later');
  if (withEl?.checked) return 'with';
  if (laterEl?.checked) return 'later';
  return 'with'; // default
}

document.addEventListener('input', (e) => {
  if (e.target && (e.target.id === 'adviser_id' || e.target.id === 'authors' || e.target.id === 'description')) {
    updateProceedButton();
  }
});


function updateProceedButton(){
  const btn   = document.getElementById('proceed-btn');
  const adv   = document.getElementById('adviser_id');
  const auth  = document.getElementById('authors');
  const desc  = document.getElementById('description');
  const passed = (window.passedInternal && window.passedExternal);

  const mode = getSelectedMode();
  if (passed) {
    showDecisionBlock(true);
    showSectionsForMode(mode);
  } else {
    showDecisionBlock(false);
  }

  const authorsOk = auth ? (auth.value && auth.value.trim().length > 0) : true;
  const adviserOk = (mode === 'later') ? true : (adv ? (adv.value && adv.value !== '') : true);
  const descOk    = desc ? (desc.value && desc.value.trim().length > 0) : true; // required by HTML too

  btn.disabled = !(passed && authorsOk && adviserOk && descOk);
}


// React when user switches mode
document.addEventListener('change', (e) => {
  if (e?.target?.name === 'adviser_mode') {
    showSectionsForMode(getSelectedMode());
    updateProceedButton();
  }
});

// React when they type in adviser/authors
document.addEventListener('input', (e) => {
  if (e.target && (e.target.id === 'adviser_id' || e.target.id === 'authors')) {
    updateProceedButton();
  }
});


async function sleep(ms){ return new Promise(r => setTimeout(r, ms)); }

/* ---------- AI Panel: NEW helpers ---------- */
const AI_STORE_KEY = 'aiPanelOpen';

function setAIHeading(rejected = true){
  const h = document.getElementById('ai-heading');
  h.textContent = rejected ? 'Why it was rejected' : 'AI Feedback';
}

function enableAIToggle(showButton){
  const btn = document.getElementById('ai-toggle-btn');
  if (showButton) {
    btn.classList.remove('hidden');
    btn.disabled = false;
  } else {
    btn.classList.add('hidden');
    btn.disabled = true;
  }
}

function setAIToggleLabel(open){
  const lbl = document.getElementById('ai-toggle-label');
  const btn = document.getElementById('ai-toggle-btn');
  lbl.textContent = 'AI Feedback';
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
}

function openAIPanel(){
  const wrap = document.getElementById('ai-feedback');
  wrap.classList.remove('hidden');
  setAIToggleLabel(true);
  try { localStorage.setItem(AI_STORE_KEY, '1'); } catch(e){}
}

function closeAIPanel(){
  const wrap = document.getElementById('ai-feedback');
  wrap.classList.add('hidden');
  setAIToggleLabel(false);
  try { localStorage.setItem(AI_STORE_KEY, '0'); } catch(e){}
}

function toggleAIPanel(){
  const wrap = document.getElementById('ai-feedback');
  const isHidden = wrap.classList.contains('hidden');
  if (isHidden) openAIPanel(); else closeAIPanel();
}

async function fetchAIFeedback(payload) {
  const res = await fetch("{{ route('titles.ai-feedback') }}", {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: JSON.stringify(payload)
  });
  return res.json();
}

function fillList(ul, items, emptyMsg='—') {
  ul.innerHTML = '';
  if (!items || !items.length) {
    const li = document.createElement('li');
    li.className = 'text-gray-500 italic';
    li.textContent = emptyMsg;
    ul.appendChild(li);
    return;
  }
  items.forEach(t => {
    const li = document.createElement('li');
    li.textContent = t;
    ul.appendChild(li);
  });
}

function renderAIFeedback(reasons=[], tips=[], reminderText=null){
  const ulR  = document.getElementById('ai-reasons');
  const ulT  = document.getElementById('ai-suggestions');
  const rem  = document.getElementById('ai-reminder');

  fillList(ulR, reasons, 'No reasons generated.');
  fillList(ulT, tips, 'No tips generated.');

  if (reminderText && typeof reminderText === 'string') {
    rem.innerHTML = '✅ <span class="font-medium">Reminder:</span> ' + escapeHtml(reminderText);
  }
}

/* ---------- Internal state ---------- */
window.passedInternal = false;
window.passedExternal = false;
window.__lastInternalData = null;
window.__lastWebData = null;
let isRunning = false;

/* ---------- Web similarity with retry ---------- */
async function fetchWebSimilarityWithRetries(title, maxTries = 2) {
  let last = null;
  for (let attempt = 1; attempt <= maxTries; attempt++) {
    const res = await fetch("{{ route('documents.check-web') }}", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ title, attempt })
    });
    last = await res.json();

    const hasResult =
      Array.isArray(last.results) &&
      last.results.length > 0 &&
      (Number(last.max_similarity) > 0 || last.results.some(r => Number(r.similarity) > 0));

    if (hasResult) return { data: last, attempts: attempt };

     showLoading(`Searching online sources for similar titles…`);

    await sleep(400 * attempt);
  }
  return { data: last ?? { max_similarity: 0, approved: true, results: [] }, attempts: maxTries };
}

/* ---------- Main ---------- */
async function startVerification(event){
  if (isRunning) return;
  isRunning = true;

  // Reset state
  window.passedInternal = false;
  window.passedExternal = false;
  updateProceedButton();
  toggleRejectHint(false);

  // Hide AI stuff until needed
  closeAIPanel();
  enableAIToggle(false);
  setAIHeading(true); // default
  // Hide description until checks pass
showDescriptionField(false);


  const title = document.getElementById('title').value.trim();
  if (title.length < 5){
    alert("Please enter a more descriptive title.");
    isRunning = false;
    return;
  }

  const verifyBtn = event?.target?.closest('button') || document.getElementById('verify-btn');
  if (verifyBtn){
    verifyBtn.disabled = true;
    verifyBtn.classList.add('opacity-60','cursor-not-allowed');
  }
  showLoading();

  /* ----- INTERNAL ----- */
  let internalPercent = 0;
  try {
    const res = await fetch("{{ route('documents.check-similarity') }}", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ title, exclude_title_id: null, limit: 10 })
    });
    if (!res.ok) throw new Error('Internal similarity failed: ' + res.status);
    const internalData = await res.json();
    window.__lastInternalData = internalData;

    internalPercent = Math.round(Number(internalData.max_similarity || 0));
    const internalApproved = internalPercent < INTERNAL_THRESHOLD;

    updateBars({
      bar: document.getElementById("similarity-bar"),
      percent: document.getElementById("similarity-percent"),
      result: document.getElementById("similarity-result")
    }, internalPercent, internalApproved);

    const internalList = document.getElementById("internal-similar-titles");
    internalList.innerHTML = "";
    if (Array.isArray(internalData.results) && internalData.results.length) {
      internalData.results.forEach((item, idx) => {
        const li = document.createElement('li');
        const safeTitle = escapeHtml(item.title || '');
        const byline = (item.authors && item.authors.length)
          ? escapeHtml(item.authors.slice(0, 5).join(', ')) + (item.authors.length > 5 ? ' et al.' : '')
          : 'Unknown author(s)';
        const year = item.year ? ` (${item.year})` : '';
        const sim  = (typeof item.similarity === 'number') ? `${item.similarity}%` : '—';
        li.className = 'p-2 bg-white rounded-md  border border-gray-300 hover:border-blue-400 hover:shadow-sm transition';
        li.innerHTML = `
          <div class="text-sm leading-snug">
            <div class="font-medium">${idx===0 ? '🔥 ' : ''}${safeTitle}</div>
            <div class="text-xs text-gray-600 mt-0.5">by ${byline}${year}</div>
            <div class="text-xs text-gray-600 mt-1">
              <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 border text-gray-700">
                Similarity: ${sim}
              </span>
            </div>
          </div>
        `;
        internalList.appendChild(li);
      });
    } else {
      internalList.innerHTML = `<li class="italic text-gray-400">No similar internal titles found.</li>`;
    }

    // NEW: internal pass check uses 20% threshold
    window.passedInternal = internalApproved;
  } catch (e) {
    console.error(e);
    updateBars({
      bar: document.getElementById("similarity-bar"),
      percent: document.getElementById("similarity-percent"),
      result: document.getElementById("similarity-result")
    }, 0, true);
    document.getElementById("internal-similar-titles").innerHTML = `<li class="italic text-gray-400">Internal check unavailable.</li>`;
    window.passedInternal = true; // don’t block on internal failure
  }

  /* ----- WEB ----- */
  updateBars({
    bar: document.getElementById("external-similarity-bar"),
    percent: document.getElementById("external-similarity-percent"),
    result: document.getElementById("external-similarity-result")
  }, 0, false, 'Waiting for web results…');

  const { data } = await fetchWebSimilarityWithRetries(title, 2);
  hideLoading();

  window.__lastWebData = data;

  // keep only results with positive similarity
  const rawResults = Array.isArray(data.results) ? data.results : [];
  const positiveResults = rawResults.filter(r => Number(r.similarity) > 0);

  // if all items are 0 (or no items), treat as 0 overall
  const anyPositive = positiveResults.length > 0;
  const webPercent = anyPositive ? Math.round(Number(data.max_similarity || 0)) : 0;

  // NEW: external pass check uses 50% threshold (ignore server-approved flag)
  const externalApproved = webPercent < EXTERNAL_THRESHOLD;

  updateBars({
    bar: document.getElementById("external-similarity-bar"),
    percent: document.getElementById("external-similarity-percent"),
    result: document.getElementById("external-similarity-result")
  }, webPercent, externalApproved);

 const webList = document.getElementById("web-similar-titles");
webList.innerHTML = "";

if (positiveResults.length) {
  positiveResults.forEach((item, idx) => {
    const li = document.createElement('li');
    const safeTitle = escapeHtml(item.title || '');
    const byline = (item.authors && item.authors.length)
      ? escapeHtml(item.authors.slice(0, 5).join(', ')) + (item.authors.length > 5 ? ' et al.' : '')
      : 'Unknown author(s)';
    const year = item.year ? ` (${item.year})` : '';
    const src  = item.source ? ` · <span class="text-[11px] text-gray-500">${escapeHtml(item.source)}</span>` : '';
    const sim  = (typeof item.similarity === 'number') ? `${item.similarity}%` : '—';
    const link = item.link ? `<a href="${item.link}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Link</a>` : '<span class="text-gray-400">No link</span>';
    li.className = 'p-2 bg-white rounded-md border border-gray-300 hover:border-green-400 hover:shadow-sm transition';
    li.innerHTML = `
      <div class="text-sm leading-snug">
        <div class="font-medium">${idx===0 ? '🔥 ' : ''}${safeTitle}</div>
        <div class="text-xs text-gray-600 mt-0.5">by ${byline}${year}${src}</div>
        <div class="text-xs text-gray-600 mt-1 flex items-center gap-2">
          <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 border text-gray-700">Similarity: ${sim}</span>
          ${link}
        </div>
      </div>
    `;
    webList.appendChild(li);
  });
} else {
  webList.innerHTML = `<li class="italic text-gray-400">No similar web titles found.</li>`;
}


  // NEW: external pass stored using 50% threshold
  window.passedExternal = externalApproved;

 const finalPass = (window.passedInternal && window.passedExternal);
toggleRejectHint(!finalPass);

// Show/Hide Description field based on verification
showDescriptionField(finalPass);

// Recompute Proceed enablement after toggling description
updateProceedButton();




  // === NEW: AI feedback respects thresholds 20/50 ===
  if (!finalPass) {
    try {
      showLoading('Generating AI feedback…');

      const internalExamples = Array.isArray(window.__lastInternalData?.results)
        ? window.__lastInternalData.results.slice(0,3).map(r => ({
            title: r.title, similarity: r.similarity, year: r.year || null
          }))
        : [];
      const webExamples = Array.isArray(window.__lastWebData?.results)
        ? window.__lastWebData.results.slice(0,3).map(r => ({
            title: r.title, similarity: r.similarity, source: r.source || null, year: r.year || null
          }))
        : [];

      const aiPayload = {
        title: title,
        internal_percent: Math.round(Number(window.__lastInternalData?.max_similarity || 0)),
        web_percent: Math.round(Number(window.__lastWebData?.max_similarity || 0)),
        internal_examples: internalExamples,
        web_examples: webExamples,
        // UPDATED rules to match your requested thresholds
        rules: [
          `internal_reject_if_percent>=${INTERNAL_THRESHOLD}`,
          `external_reject_if_percent>=${EXTERNAL_THRESHOLD}`
        ]
      };

      const aiRes = await fetchAIFeedback(aiPayload);
      hideLoading();

      if (aiRes?.ok) {
        renderAIFeedback(aiRes.reasons, aiRes.tips, aiRes.reminder);
      } else {
        renderAIFeedback(
          ['AI feedback unavailable.'],
          ['Narrow the scope and specify method.', 'Define population/context clearly.', 'State what’s novel in your approach.'],
          null
        );
      }

      setAIHeading(true);
      enableAIToggle(true);
      let wantOpen = false;
      try { wantOpen = localStorage.getItem(AI_STORE_KEY) === '1'; } catch(e){}
      if (wantOpen) openAIPanel(); else closeAIPanel();

    } catch (err) {
      console.error(err);
      hideLoading();
      renderAIFeedback(
        ['Error generating AI feedback.'],
        ['Shorten the title and add a unique angle.', 'Specify domain, population, and method.', 'Avoid boilerplate or generic phrases.'],
        null
      );

      setAIHeading(true);
      enableAIToggle(true);
      closeAIPanel();
    }
  } else {
    enableAIToggle(false);
    closeAIPanel();
  }

  if (verifyBtn){
    verifyBtn.disabled = false;
    verifyBtn.classList.remove('opacity-60','cursor-not-allowed');
  }
  isRunning = false;
}
</script>

</x-userlayout>
