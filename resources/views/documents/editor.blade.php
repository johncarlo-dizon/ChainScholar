 <!-- documents/editor.blade.php -->
<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl text-white font-semibold mb-4">
     {{ $document->chapter ?? 'Untitled Chapter' }} 
        </h2>
        <p class="text-sm-5 text-gray-300 mt-1">    {{$title->title }}</p>
    </div>

    <div class="container mx-auto px-4 py-8">
        <form 
            action="{{ route('documents.update', $document) }}" 
            method="POST" 
            class="document-form" 
            id="doc-form">
            @csrf
            @method('PUT')

            <input type="hidden" name="title_id" value="{{ $document->title_id }}">
            <input type="hidden" name="chapter" value="{{ $document->chapter }}">

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left: Content Editor -->
                <div class="main-container w-full lg:w-2/3">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <div class="mb-6">
                                <label class="block mb-2 font-bold text-blue-600">Chapter</label>
                                <input 
                                    type="text" 
                                    value="{{ $document->chapter }}" 
                                    disabled 
                                    class="w-full bg-gray-100 border border-gray-300 rounded-md px-4 py-3 text-lg"
                                >
                            </div>

                            <div class="mb-6">
                                <label for="editor" class="block mb-2 font-bold text-blue-600">Document Content</label>
                                <div class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-word-count editor-container_include-fullscreen" id="editor-container">
                                    <div class="editor-container__editor">
                           @php
    $prefillContent = session('templateContent') ?? old('content', $document->content ?? '');
@endphp

<textarea 
    name="content" 
    id="editor" 
    class="min-h-[600px] w-full p-4 bg-white border border-gray-300 rounded-md hidden"
>{{ $prefillContent }}</textarea>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

          <!-- Right: Sidebar -->
<div class="w-full lg:w-1/3 lg:sticky lg:top-6 h-fit">

    <!-- Default Sidebar Panel -->
    <div id="default-sidebar" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full space-y-8">
      

    


<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-700">Document Info</h3>
    </div>

    <div id="sections-outline" class="text-sm text-gray-600 space-y-2">
        <p class="text-gray-500">Start typing… sections will appear here.</p>
    </div>
   <div id="editor-word-count" class="text-sm text-gray-500 mt-0 pt-0">
       <!-- Word count will be injected here -->
        </div>
  
</div>



         <div class="space-y-4">
  <div class="flex items-center justify-between">
    <h3 class="text-lg font-semibold text-gray-700">Plagiarism Checker</h3>
       <button type="button" id="btnViewMatches"
      class="px-2 py-2 shadow-sm text-gray-700 text-sm rounded-lg hover:text-gray-500  transition">
      Internal Matches
    </button>
    <button type="button" id="btnCopyleaks"
  class="px-2 py-2 shadow-sm text-gray-700 text-sm rounded-lg hover:text-gray-500 transition">
   External Matches
</button>

  </div>

  <div class="flex gap-3">
   

    <div id="plagiarism-result" class="text-sm text-gray-700 hidden"></div>
  
  </div>
</div>

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-700">Adviser Message</h3>
        @if(!empty($adviserNote))
            <span class="text-xs text-gray-500">
                Updated {{ $adviserNote->updated_at->diffForHumans() }}
            </span>
        @endif
    </div>

    @if(!empty($adviserNote))
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-2
            max-h-40 overflow-y-auto overflow-x-hidden
            w-full max-w-full min-w-0">
  <div class="whitespace-pre-wrap break-words break-all text-sm text-gray-800">
    {{ $adviserNote->content }}
  </div>
</div>

    @else
        <div class="rounded-lg border border-dashed border-gray-200 p-4 text-sm text-gray-500">
            No adviser note yet for this chapter.
        </div>
    @endif
</div>

@php
  $myNote = \App\Models\StudentNote::where('title_id', $title->id)
            ->where('document_id', $document->id)
            ->where('student_id', auth()->id())
            ->first();
@endphp

<div id="studentNotePanel"
     data-save-url="{{ route('student.notes.save', [$title, $document]) }}"
     data-has-note="{{ $myNote ? '1' : '0' }}"
     class="space-y-3">

  <div class="flex items-center justify-between">
    <h3 class="text-lg font-semibold text-gray-700">Message Your Adviser</h3>
    @if($myNote)
      <span id="studentNoteUpdatedAt" class="text-xs text-gray-500">
        Updated {{ $myNote->updated_at->diffForHumans() }}
      </span>
    @else
      <span id="studentNoteUpdatedAt" class="text-xs text-gray-500 hidden"></span>
    @endif
  </div>

  <textarea id="studentNoteTextarea" rows="4"
    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
    placeholder="(Optional) Type your note…">{{ old('message', $myNote->content ?? '') }}</textarea>

  <div class="flex items-center gap-3">
    <button type="button" id="studentNoteSaveBtn"
      class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
      {{ $myNote ? 'Update Message' : 'Send Message' }}
    </button>
    <span id="studentNoteStatus" class="text-sm text-gray-500"></span>
  </div>
</div>

<script>
(() => {
  const panel   = document.getElementById('studentNotePanel');
  if (!panel) return;

  const url     = panel.dataset.saveUrl;
  const ta      = document.getElementById('studentNoteTextarea');
  const btn     = document.getElementById('studentNoteSaveBtn');
  const status  = document.getElementById('studentNoteStatus');
  const updated = document.getElementById('studentNoteUpdatedAt');
  let   hasNote = panel.dataset.hasNote === '1';

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

  function setStatus(msg, tone='muted'){
    status.textContent = msg || '';
    status.classList.remove('text-emerald-600','text-rose-600','text-gray-500');
    status.classList.add(tone === 'ok' ? 'text-emerald-600' : tone === 'err' ? 'text-rose-600' : 'text-gray-500');
  }

  async function saveNote(){
    const message = (ta.value || '').trim(); // empty allowed (means clear)
    btn.disabled = true;
    btn.classList.add('opacity-50','cursor-not-allowed');
    setStatus(message ? 'Saving…' : (hasNote ? 'Removing…' : 'Nothing to save…'));

    try{
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ message })
      });

      let data = {};
      try { data = await res.json(); } catch {}

      if (!res.ok || data.ok === false) {
        throw new Error(data?.message || 'Save failed.');
      }

      // statuses: created | updated | deleted | noop
      if (data.status === 'deleted' || data.status === 'noop') {
        hasNote = false;
        btn.textContent = 'Send Message';
        updated?.classList.add('hidden');
        setStatus(data.status === 'deleted' ? 'Message cleared.' : 'No changes.', 'ok');
      } else {
        hasNote = true;
        btn.textContent = 'Update Message';
        if (updated){
          updated.textContent = 'Updated just now';
          updated.classList.remove('hidden');
        }
        setStatus('Saved.', 'ok');
      }
    }catch(e){
      setStatus(e.message || 'Error. Try again.', 'err');
    }finally{
      btn.disabled = false;
      btn.classList.remove('opacity-50','cursor-not-allowed');
      setTimeout(() => setStatus(''), 1500);
    }
  }

  btn.addEventListener('click', saveNote);
})();
</script>






    <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700">Actions</h3>
            </div>
            <div class="space-y-4">
                <div class="flex flex-col">
                    <a href="{{ route('templates.index', ['use_for' => 'chapter', 'document_id' => $document->id]) }}"
                       class="text-sm text-blue-500 transition hover:text-blue-700">
                        Use Template
                    </a>

                    @if(session()->has('templateContent') && !session()->has('templateUndone'))
                        <a href="{{ route('documents.undoTemplate', $document) }}"
                           class="text-sm text-blue-500 transition hover:text-blue-700">
                            Undo Template
                        </a>
                    @endif

                    <a href="javascript:void(0);" onclick="toggleSubmitForm()"
                       class="text-sm text-blue-500 transition hover:text-blue-700">
                       Upload Research
                    </a>
                </div>
            </div>
        </div>








        <div class="pt-2 border-t border-gray-200">
            <div class="flex justify-between">
                <a href="{{ route('titles.chapters', $document->title_id) }}"
                   class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition duration-200">
                    Back to Chapters
                </a>
                <button type="submit"      id="saveBtn"
                        class="flex-1 text-center ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-sm focus:ring-2 focus:ring-blue-300">
                    Save
                </button>
            </div>
        </div>
    </div>
      </form>

    <!-- Submit Final Document Form Panel -->
    <div id="submit-sidebar" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
        <form action="{{ route('documents.submit', ['title_id' => $document->title_id]) }}" method="POST" enctype="multipart/form-data" id="submit-final-form">
            @csrf
    <input type="hidden" name="finaldocument_id" value="{{ $document->id }}">
   


      <div class="space-y-5">


    <!-- Authors -->
    <div>
        <label class="block text-gray-700 font-medium mb-1">Authors <span class="text-sm text-gray-500">(comma-separated)</span></label>
        <input type="text" name="authors" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            placeholder="e.g., Juan Dela Cruz, Maria Santos"      value="{{ $title->authors ?? '' }}">
    </div>


    <!-- Abstract -->
    <div>
        <label class="block text-gray-700 font-medium mb-1">Abstract</label>
        <textarea name="abstract" rows="4" required
            class="w-full border border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
            placeholder="Enter a concise summary of your research..."></textarea>
    </div>

 

    <!-- Research Type -->
    <div>
        <label class="block text-gray-700 font-medium mb-1">Research Type</label>
        <select name="research_type" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white">
            <option value="" disabled selected>Select type...</option>
            <option value="Capstone">Capstone</option>
            <option value="Thesis">Thesis</option>
            <option value="Journal">Journal</option>
            <option value="Funded">Funded</option>
            <option value="Independent">Independent</option>
        </select>
    </div>
</div>


            <textarea name="final_content" id="finalContent" class="hidden"></textarea>

           <div class="pt-2 border-t border-gray-200">
             <div class="flex justify-between">
                <button type="button" onclick="toggleSubmitForm()"  class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition duration-200">
                    ← Cancel
                </button>
                <button type="submit"
                  id="submitBtn"
 
                        onclick="prepareFinalContent()"
               class="flex-1 text-center ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-sm focus:ring-2 focus:ring-blue-300">
                    Upload Research
                </button>
            </div>
           </div>


  



        </form>
    </div>

</div>
<!-- Offcanvas: Plagiarism Matches -->
<div id="plagOffcanvas" class="fixed inset-0 z-[9999] hidden">
  <!-- dim -->
  <div id="plagDim" class="absolute inset-0 bg-black/40"></div>

  <!-- panel -->
  <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white shadow-xl flex flex-col">
    <div class="border-b border-gray-100 px-4 py-3 flex items-center justify-between">
      <h3 class="text-lg font-semibold">Plagiarism Matches (from Chapter 1 onward)</h3>
      <button type="button" id="plagClose" class="p-2 rounded hover:bg-gray-100" aria-label="Close">✕</button>
    </div>

    <div id="plagBody" class="p-4 overflow-y-auto grow">
      <!-- loader / results injected here -->
    </div>
  </div>
</div>


    <!-- CKEditor Scripts -->
    <link rel="stylesheet" href="{{ asset('assets/editor.css') }}">
    <script src="{{ asset('assets/editor.js') }}"></script>



    <script>
(function(){
  const offcanvas = document.getElementById('plagOffcanvas');
  const dim       = document.getElementById('plagDim');
  const closeBtn  = document.getElementById('plagClose');
  const bodyBox   = document.getElementById('plagBody');
  const btnView   = document.getElementById('btnViewMatches');

  function openOffcanvas(){ offcanvas.classList.remove('hidden'); }
  function closeOffcanvas(){ offcanvas.classList.add('hidden'); }

  dim?.addEventListener('click', closeOffcanvas);
  closeBtn?.addEventListener('click', closeOffcanvas);

  btnView?.addEventListener('click', async ()=>{
    const el = document.querySelector('.ck-content');
    if(!el){
      alert('Editor not ready.');
      return;
    }

    openOffcanvas();
    bodyBox.innerHTML = `
      <div class="flex items-center gap-2 text-gray-600">
        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"/><path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"/></svg>
        <span>Scanning for detailed matches…</span>
      </div>
    `;

    try{
      const res = await fetch("{{ route('documents.checkPlagiarismDetailed') }}", {
        method: 'POST',
        headers: {
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({
          content_html: el.innerHTML,          // send HTML for better paragraph context
          document_id: {{ $document->id }}     // current doc id
        })
      });

      const data = await res.json();
      const matches = Array.isArray(data.matches) ? data.matches : [];
      const score   = Number(data.score ?? 0);

      if(!matches.length){
        bodyBox.innerHTML = `
          <div class="space-y-3">
            <div class="text-sm text-gray-500">Overall Score: <strong>${score}%</strong></div>
            <div class="p-4 rounded shadow bg-gray-50 text-gray-700">No matches found for the current settings.</div>
          </div>`;
        return;
      }

      const cards = matches.map(m => `
  <div class="shadow overflow-hidden mb-4">
    <!-- header -->
    <div class="px-4 py-2 bg-gray-50 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        <span class="font-semibold">Similarity:</span> ${m.percent}%
      </div>
      ${m.source_url ? `<a class="text-xs text-blue-600 hover:underline" href="${m.source_url}" target="_blank" rel="noopener">Open source</a>` : ''}
    </div>

    <!-- row 1: YOUR content -->
    <div class="p-4">
      <div class="text-xs font-semibold text-gray-500 mb-1">Your content</div>
      <pre class="whitespace-pre-wrap text-sm leading-relaxed text-gray-800">${escapeHtml(m.your_excerpt)}</pre>
    </div>

    <!-- tiny source line (no excerpt) -->
    <div class="px-4 pb-4 text-xs text-gray-500">
      Source: ${escapeHtml(m.source_title || 'External source')}
    </div>
  </div>
`).join('');


      bodyBox.innerHTML = `
        <div class="mb-3 text-sm text-gray-600">
          Overall Max Similarity: <strong>${score}%</strong> • Showing top ${matches.length} matches
        </div>
        ${cards}
      `;
    }catch(err){
      bodyBox.innerHTML = `
        <div class="p-4 rounded border bg-red-50 text-red-700">
          Error generating matches. Please try again.
        </div>`;
    }
  });

  // simple HTML escaper to keep excerpts safe
  function escapeHtml(s){
    return (s ?? '').toString()
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;');
  }
})();
</script>
















    <script>
        window.addEventListener("beforeunload", function () {
            fetch("{{ route('clear.template.session') }}");
        });


function prepareFinalContent() {
    const contentElement = document.querySelector('.ck-content');
    if (!contentElement) {
        alert("Editor content not found.");
        return false;
    }

    // Clone the content to avoid modifying the DOM directly
    const cloned = contentElement.cloneNode(true);

    // Remove any placeholder attributes
    cloned.querySelectorAll('[data-placeholder]').forEach(el => {
        el.removeAttribute('data-placeholder');
    });

    // Clean HTML to submit
    const cleanHtml = cloned.innerHTML.trim();

    // Set to hidden field
    document.getElementById('finalContent').value = cleanHtml;

    return true;
}


    function toggleSubmitForm() {
        const defaultSidebar = document.getElementById('default-sidebar');
        const submitSidebar = document.getElementById('submit-sidebar');

        defaultSidebar.classList.toggle('hidden');
        submitSidebar.classList.toggle('hidden');
    }
</script>




<script>
/** ===== Target word counts ===== */
const SECTION_STANDARDS = {
  'Abstract': 300,
  'Introduction': 1000,
  'Background of the Study': 800,
  'Statement of the Problem': 500,
  'Significance of the Study': 400,
  'Scope and Delimitations': 300,
  'Review of Related Literature': 2000,
  'Methodology': 1500,
  'Results': 1000,
  'Discussion': 1000,
  'Conclusion': 300,
  'Recommendations': 300
};

/** Canonical names + variants */
const SECTION_ALIASES = [
  { key: 'Introduction', patterns: [/^intro(duction)?$/i] },
  { key: 'Background of the Study', patterns: [/^background( of (the )?study)?$/i] },
  // Map “General Problem” and “Specific Problems” into ONE key
  { key: 'Statement of the Problem', patterns: [
      /^(statement of )?the problem$/i,
      /^problems?\s*statement$/i,
      /^general problems?$/i,
      /^general problem$/i,
      /^specific problems?$/i
    ]
  },
  { key: 'Significance of the Study', patterns: [/^significance( of (the )?study)?$/i] },
  { key: 'Scope and Delimitations', patterns: [/^scope( and)? (delimitation|delimitations)$/i] },
  { key: 'Review of Related Literature', patterns: [/^(review|related (studies|literature))(.*)?$/i, /^rrl$/i] },
  { key: 'Methodology', patterns: [/^method(s|ology)?$/i, /^research methodology$/i] },
  { key: 'Results', patterns: [/^results?$/i, /^findings?$/i] },
  { key: 'Discussion', patterns: [/^discussion$/i, /^analysis$/i] },
  { key: 'Conclusion', patterns: [/^conclusion(s)?$/i] },
  { key: 'Recommendations', patterns: [/^recommendation(s)?$/i] },
  { key: 'Abstract', patterns: [/^abstract$/i] },
  // helpers
  { key: 'Chapter', patterns: [/^chapter\s+[ivx\d]+$/i] },
  { key: 'ChapterSubtitle', patterns: [/^[A-Z][A-Z\s\-:&]+$/] },
];

function normalizeHeadingLabel(raw) {
  const text = (raw || '').trim().replace(/\s+/g, ' ');
  for (const {key, patterns} of SECTION_ALIASES) {
    if (patterns.some(rx => rx.test(text))) {
      if (key === 'Chapter' || key === 'ChapterSubtitle') return text;
      return key;
    }
  }
  return text.replace(/\w\S*/g, w => w[0].toUpperCase() + w.slice(1).toLowerCase());
}

/** DOM helpers */
function isTrueHeading(node){ return node && node.nodeType===1 && /^(H1|H2|H3|H4|H5|H6)$/i.test(node.tagName); }
function hasCenterAlign(node){ const s=(node.getAttribute('style')||'').toLowerCase(); return s.includes('text-align:center'); }
function textOnly(node){ return (node?.innerText || '').replace(/\u00a0/g,' ').trim(); }

/** Pseudo headings like <p><strong>Introduction</strong></p> */
function isPseudoHeading(node){
  if (!node || node.nodeType!==1 || node.tagName!=='P') return false;
  const t=textOnly(node);
  if (!t || t.length>120) return false;
  const hasStrong=node.querySelector('strong,b')!==null;
  const looksLikeTitle=/^[A-Z0-9\s\-:()]+$/.test(t) || hasStrong || hasCenterAlign(node);
  const isKnown=SECTION_ALIASES.some(({patterns})=>patterns.some(rx=>rx.test(t)));
  return looksLikeTitle && isKnown;
}

/** Title/front‑matter hints */
const TITLE_PAGE_HINTS=[/a research presented/i,/in partial fulfillment/i,/institute of/i,/holy cross college/i,/submitted by/i,/sta\.?\s*ana/i,/^_{3,}$/i,/^[—_]+$/i];
function isFrontMatter(node){
  const t=textOnly(node);
  if (!t) return true;
  if (node.querySelector('img')) return true;
  if (hasCenterAlign(node)) return true;
  if (TITLE_PAGE_HINTS.some(rx=>rx.test(t))) return true;
  if (/^[A-Z0-9\s_.—-]+$/.test(t) && t.length<=80) return true;
  return false;
}

/** Count words */
function countWords(s){ const tokens=s.match(/\b[\p{L}\p{N}’'-]+\b/gu); return tokens?tokens.length:0; }

/** Extract sections from CKEditor DOM */
function extractSections(){
  const root=document.querySelector('.ck-content');
  if (!root) return [];
  const blocks=Array.from(root.children);
  const sections=[];
  let current=null, started=false, i=0;

  function startSection(label){ current={ name: normalizeHeadingLabel(label), words:0 }; sections.push(current); }
  function addTextFrom(node){ const t=textOnly(node); if (t) current.words+=countWords(t); }

  while(i<blocks.length){
    const node=blocks[i];

    if (!started){
      if (isTrueHeading(node) || isPseudoHeading(node)){
        let title=normalizeHeadingLabel(textOnly(node));
        if (/^chapter\s+[ivx\d]+$/i.test(title)){
          const next=blocks[i+1]; const t2=next?textOnly(next):'';
          if (next && isPseudoHeading(next) && /^[A-Z][A-Z\s\-:&]+$/.test(t2)){
            title = `${title} — ${t2}`; i++;
          }
        }
        startSection(title); started=true; i++; continue;
      }
      if (isFrontMatter(node)){ i++; continue; }
      startSection('Body'); started=true;
    }

    if (isTrueHeading(node) || isPseudoHeading(node)){
      let title=normalizeHeadingLabel(textOnly(node));
      if (/^chapter\s+[ivx\d]+$/i.test(title)){
        const next=blocks[i+1]; const t2=next?textOnly(next):'';
        if (next && isPseudoHeading(next) && /^[A-Z][A-Z\s\-:&]+$/.test(t2)){
          title = `${title} — ${t2}`; i++;
        }
      }
      startSection(title); i++; continue;
    }

    addTextFrom(node); i++;
  }

  return sections.filter(s=>s.words>0);
}

/** Merge same-named sections (e.g., General + Specific Problems) */
function combineSameNamedSections(sections){
  const map=new Map();
  for (const s of sections){
    const key=s.name;
    const prev=map.get(key);
    if (prev) prev.words += s.words; else map.set(key, {...s});
  }
  return Array.from(map.values());
}

/** Render to sidebar: ONLY sections that have targets → show % only */
function renderSectionsOutline(){
  const box = document.getElementById('sections-outline');
  if (!box) return;

  const HIDE = [/^chapter\s+/i];
  const raw = extractSections().filter(s => !HIDE.some(rx => rx.test(s.name)));
  if (!raw.length){
    box.innerHTML = '<p class="text-gray-500">No sections detected yet.</p>';
    return;
  }

  const merged = combineSameNamedSections(raw);
  const withTargets = merged.filter(s => SECTION_STANDARDS[s.name] != null);

  const items = withTargets.map(s=>{
    const target = SECTION_STANDARDS[s.name];
    const pct = Math.min(100, Math.round((s.words / target) * 100));
    const deg = pct * 3.6; // 100% -> 360deg

    return `
      <div class="flex items-center justify-between py-1">
        <div class="flex items-center gap-2">
          <!-- Circle progress inverted (right-to-left) -->
          <span class="relative inline-block w-4.5 h-4.5 rounded-full"
                style="background: conic-gradient(#2563eb ${deg}deg, #e5e7eb 0deg);
                       transform: rotate(180deg);">
            <span class="absolute inset-[3px] bg-white rounded-full"></span>
          </span>
          <span class="truncate">${s.name}</span>
        </div>
        <span class="font-medium">${pct}%</span>
      </div>`;
  }).join('');

  box.innerHTML = `
    <div class="space-y-1">
      ${items || '<p class="text-gray-500">No targeted sections yet.</p>'}
    </div>`;
}


/** Live updates */
document.addEventListener('DOMContentLoaded', ()=>{
  const tick=()=>renderSectionsOutline();
  const wait=setInterval(()=>{
    const el=document.querySelector('.ck-content');
    if (el){
      clearInterval(wait);
      tick();
      let debounce;
      const mo=new MutationObserver(()=>{
        clearTimeout(debounce);
        debounce=setTimeout(tick,300);
      });
      mo.observe(el,{subtree:true,childList:true,characterData:true,attributes:true});
    }
  },150);
});
</script>






<script>
// ---------------- GLOBAL GATE (shared) ----------------
/**
 * Global pass threshold for BOTH checkers.
 * Buttons are enabled ONLY IF:
 *   - window.__internalScore is a number AND < PASS_THRESHOLD
 *   - window.__externalScore is a number AND < PASS_THRESHOLD
 */
const PASS_THRESHOLD = 20;

// These are set by the internal & external scripts, and reset to null on edits.
window.__internalScore = null;
window.__externalScore = null;

// Buttons / status nodes
const saveBtn   = document.getElementById('saveBtn');
const submitBtn = document.getElementById('submitBtn');
const resultBox = document.getElementById('plagiarism-result');

function setButtonsDisabled(disabled, reason = '') {
  [saveBtn, submitBtn].forEach(btn => {
    if (!btn) return;
    btn.disabled = disabled;
    btn.classList.toggle('opacity-50', disabled);
    btn.classList.toggle('cursor-not-allowed', disabled);
    btn.classList.toggle('hover:bg-blue-700', !disabled);
  });
  if (resultBox) {
    resultBox.classList.remove('hidden');
    resultBox.innerHTML = reason ? `<span class="text-gray-600">${reason}</span>` : '';
  }
}

function buildGateReason() {
  const hasInternal = typeof window.__internalScore === 'number';
  const hasExternal = typeof window.__externalScore === 'number';
  const lines = [];
  if (!hasInternal) lines.push('Run the internal checker.');
  if (!hasExternal) lines.push('Run the external (Copyleaks) checker.');
  if (hasInternal && window.__internalScore >= PASS_THRESHOLD) {
    lines.push(`Internal score ${window.__internalScore}% must be below ${PASS_THRESHOLD}%.`);
  }
  if (hasExternal && window.__externalScore >= PASS_THRESHOLD) {
    lines.push(`External score ${window.__externalScore}% must be below ${PASS_THRESHOLD}%.`);
  }
  return lines.join(' ');
}

function updatePlagGate(optionalReason) {
  const hasInternal = typeof window.__internalScore === 'number';
  const hasExternal = typeof window.__externalScore === 'number';
  const internalOk  = hasInternal && window.__internalScore < PASS_THRESHOLD;
  const externalOk  = hasExternal && window.__externalScore < PASS_THRESHOLD;
  const enabled     = internalOk && externalOk;

  setButtonsDisabled(!enabled, enabled ? '' : (optionalReason || buildGateReason()));

  // Show combined status line
  if (resultBox) {
    const internalTxt = hasInternal ? `${window.__internalScore}%` : '—';
    const externalTxt = hasExternal ? `${window.__externalScore}%` : '—';
    const allOk       = enabled ? `<span class="text-green-600">Ready to save/submit ✅</span>` :
                                   `<span class="text-red-600">Not ready</span>`;
    resultBox.classList.remove('hidden');
    resultBox.innerHTML =
      `<div class="text-sm">
         <div>Internal: <strong>${internalTxt}</strong> | External: <strong>${externalTxt}</strong></div>
         <div class="mt-1">${allOk}${enabled ? '' : `<span class="text-gray-600"> — ${buildGateReason()}</span>`}</div>
       </div>`;
  }
}

// ---------------- INTERNAL (my database) LIVE CHECK ----------------
const MIN_CHARS_TO_CHECK = 40; // avoid noise when the doc is still empty
const DISPLAY_MIN = 0;

// helpers to read content
function getEditorHTML() {
  const el = document.querySelector('.ck-content');
  return el ? el.innerHTML.trim() : null;
}
function getEditorVisibleTextLength() {
  const el = document.querySelector('.ck-content');
  return el ? el.innerText.trim().length : 0;
}

async function checkPlagiarism() {
  const html = getEditorHTML();
  if (html === null) {
    setButtonsDisabled(true, 'Editor not ready yet…');
    return;
  }
  if (getEditorVisibleTextLength() < MIN_CHARS_TO_CHECK) {
    // Not enough content yet. Internal score is unknown.
    window.__internalScore = null;
    updatePlagGate('Type more content, then run the checker.');
    return;
  }

  if (resultBox) {
    resultBox.classList.remove('hidden');
    resultBox.innerHTML = `
      <div class="flex items-center space-x-2 text-gray-600">
        <svg class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span>Checking (internal)…</span>
      </div>
    `;
  }

  try {
    const response = await fetch(`{{ route('documents.checkPlagiarismLive') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        content_html: html,
        document_id: {{ $document->id }},
        min_percent: DISPLAY_MIN
      })
    });

    const data  = await response.json();
    const score = Number(data.score ?? 0);

    // Set global internal score & update gate
    window.__internalScore = isNaN(score) ? null : score;
    updatePlagGate();
  } catch (error) {
    // Internal failed -> require re-run
    window.__internalScore = null;
    updatePlagGate('Error checking (internal). Please try again.');
  }
}

// ---------- init: keep disabled until both checks pass ----------
document.addEventListener('DOMContentLoaded', () => {
  // On first load, we haven't run either checker yet.
  window.__internalScore = null;
  // __externalScore will be set by the Copyleaks panel when used.
  updatePlagGate('Run both plagiarism checks.');

  // Wait for CKEditor .ck-content to exist, then set observers
  const waitForEditor = setInterval(() => {
    const el = document.querySelector('.ck-content');
    if (el) {
      clearInterval(waitForEditor);

      // Debounced re-check of internal (optional). Also, edits invalidate BOTH scores.
      let debounce;
      const observer = new MutationObserver(() => {
        // Any content change invalidates previous results (require re-check)
        window.__internalScore = null;
        window.__externalScore = null;
        updatePlagGate('Content changed. Run internal and external checkers again.');
        clearTimeout(debounce);
        // If you want auto-run internal after idle, keep this:
        debounce = setTimeout(checkPlagiarism, 1200);
      });
      observer.observe(el, { subtree: true, characterData: true, childList: true });

      // Optionally auto-run an initial internal check once editor is ready
      setTimeout(checkPlagiarism, 500);
    }
  }, 150);
});

// Expose to any button you might wire elsewhere
window.checkPlagiarism = checkPlagiarism;
</script>



<script>
(() => {
  const btnExternal = document.getElementById('btnCopyleaks');
  const offcanvas   = document.getElementById('plagOffcanvas');
  const bodyBox     = document.getElementById('plagBody');
    // --- polling state ---
  let pollingTimer = null;

  function stopPolling() {
    if (pollingTimer) {
      clearInterval(pollingTimer);
      pollingTimer = null;
    }
  }
  window.addEventListener('beforeunload', stopPolling);

let lastProgress = 0;
let lastStepIndex = 1;
function resetPhaseTracker(){ lastProgress = 0; lastStepIndex = 1; }

function renderPhaseBar(meta) {
  const steps = Array.isArray(meta?.steps) ? meta.steps : [
    {key:'queued',label:'Queued'},
    {key:'scanning',label:'Scanning'},
    {key:'results_ready',label:'Results ready'},
    {key:'export_scheduled',label:'Export scheduled'},
    {key:'exporting',label:'Exporting results'},
    {key:'finalizing',label:'Done'},
  ];

  const current  = meta?.phase || 'queued';
  const rawPct   = Number.isFinite(+meta?.progress_percent) ? +meta.progress_percent : 0;
  const rawStep  = Number.isFinite(+meta?.step_index)       ? +meta.step_index       : 1;

  // monotonic progress + step
  const progress = Math.max(lastProgress, Math.max(0, Math.min(100, rawPct)));
  const stepIdx  = Math.max(lastStepIndex, Math.max(1, Math.min(steps.length, rawStep)));
  lastProgress   = progress;
  lastStepIndex  = stepIdx;

  // don’t show 100% until terminal
  const isTerminal = ['completed','exported','finalizing'].includes(meta?.status) || current === 'finalizing';
  const shownPct   = isTerminal ? progress : Math.min(progress, 99);

  // dots grid (labels are in the same column as their dot)
  const colsStyle  = `grid-template-columns: repeat(${steps.length}, minmax(0,1fr));`;

  const dotItems = steps.map((s, i) => {
    const done    = i < (stepIdx - 1) || isTerminal;
    const active  = i === (stepIdx - 1) && !isTerminal;
    const dotCls  = done ? 'bg-emerald-500' : active ? 'bg-blue-600' : 'bg-gray-300';
    const lblCls  = done ? 'text-emerald-700' : active ? 'text-blue-700' : 'text-gray-500';

    return `
      <div class="flex flex-col items-center">
        <div class="relative">
          <!-- white halo to cleanly cut the background line under the dot -->
          <span class="absolute -inset-1 rounded-full bg-white"></span>
          <span class="relative block w-2.5 h-2.5 rounded-full ${dotCls}"></span>
        </div>
        <div class="mt-1 text-[11px] leading-tight ${lblCls} whitespace-nowrap">${s.label}</div>
      </div>
    `;
  }).join('');

  return `
    <div class="space-y-2 select-none">
      <div class="flex items-center justify-between">
        <div class="text-sm font-medium text-gray-800">Status: ${current.replace(/_/g,' ')}</div>
        <div class="text-xs text-gray-500">${Math.round(shownPct)}%</div>
      </div>

      <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
        <div class="h-2 bg-blue-600" style="width:${shownPct}%"></div>
      </div>

      <!-- Steps line + dots -->
      <div class="relative mt-3">
        <!-- single continuous connector line -->
        <div class="absolute left-2 right-2 top-1.5 h-0.5 bg-gray-200"></div>

        <!-- evenly spaced dots with labels underneath -->
        <div class="grid gap-0" style="${colsStyle}">
          ${dotItems}
        </div>
      </div>
    </div>
  `;
}





  // Kill polling when offcanvas closes
  function openOffcanvas(){ 
    resetPhaseTracker();   
    offcanvas.classList.remove('hidden'); }
  function closeOffcanvas(){ 
    offcanvas.classList.add('hidden'); 
    stopPolling();
  }

  // Cache-busting GET
  async function getJsonNoCache(url) {
    const u = new URL(url, window.location.origin);
    u.searchParams.set('_', Date.now().toString());
    const res = await fetch(u.toString(), {
      headers: { 'Accept':'application/json', 'Cache-Control':'no-cache, no-store' },
      cache: 'no-store'
    });
    return await res.json();
  }


  const esc = s => (s ?? '').toString().replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');

    // (already defined above)
  document.getElementById('plagDim')?.addEventListener('click', closeOffcanvas);
  document.getElementById('plagClose')?.addEventListener('click', closeOffcanvas);
  // Optional: refresh when tab regains focus (helps if webhooks landed while user switched tabs)
  window.addEventListener('visibilitychange', () => {
    if (!document.hidden && !offcanvas.classList.contains('hidden')) {
      // soft refresh
      openAndLoadLatest();
    }
  });


  btnExternal?.addEventListener('click', openAndLoadLatest);

  async function openAndLoadLatest() {
    openOffcanvas();
    bodyBox.innerHTML = `
      <div class="flex items-center gap-2 text-gray-600">
        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
          <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
        </svg>
        <span>Loading external matches…</span>
      </div>
    `;

    try {
      const url = `{{ route('documents.copyleaks.status', $document) }}`;
      const data = await getJsonNoCache(url);


      if (!data || data.status === 'none') {
        renderIdle('No previous external scans yet for this chapter.');
        return;
      }
           if (data.status === 'running' || data.status === 'queued') {
         renderRunning(data);
        // Begin polling every 3s until a terminal state is reached
        stopPolling();
        pollingTimer = setInterval(async () => {
          try {
            const next = await getJsonNoCache(`{{ route('documents.copyleaks.status', $document) }}`);
            if (!next || next.status === 'error') {
              stopPolling();
              renderIdle(next?.error || 'Scan failed. Try re-running.');
              return;
            }
            if (next.status === 'completed' || next.status === 'exported') {
              stopPolling();
              renderResults(next);
            }
          } catch {
            // network hiccup: keep polling
          }
        }, 3000);
        return;
      }

      if (data.status === 'error') {
        renderIdle(data.error || 'Scan failed. Try re-running.');
        return;
      }
      renderResults(data);
    } catch (e) {
      renderIdle('Failed to load external results.');
    }
  }

  function renderIdle(note) {
    bodyBox.innerHTML = `  <div class="space-y-4">
        ${renderPhaseBar({ phase:'queued', step_index:1, step_total:6, progress_percent:0 })}
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">External Plagiarism (Copyleaks)</h3>
          <button type="button" class="px-3 py-1.5 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                  id="btnRescanExternal">
            Start scan
          </button>
        </div>
        ${note ? `<div class="p-3 rounded bg-yellow-50 text-yellow-800 text-sm">${esc(note)}</div>` : ``}
      </div>
    `;
    document.getElementById('btnRescanExternal')?.addEventListener('click', startExternalScan);
  }

      function renderRunning(meta) {
    bodyBox.innerHTML = `
      <div class="space-y-4 text-gray-700">
        ${renderPhaseBar(meta)}
        <div class="flex items-center gap-2">
          <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
            <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
          </svg>
          <span>Checking external sources…</span>
        </div>
        <div><button type="button" id="btnRefreshExternal" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">Refresh</button></div>
      </div>
    `;
    document.getElementById('btnRefreshExternal')?.addEventListener('click', openAndLoadLatest);
    // (polling code remains as you already added)
  }






  function renderResults(data) {
    const sourceMax   = Number(data.source_max ?? 0);
    const docAgg      = Number(data.doc_aggregated ?? 0);
    const matches     = Array.isArray(data.matches) ? data.matches : [];
    const wordsTotal  = Number(data.doc_total_words ?? 0);


      const highlightHtml = (data.plagiarized_highlight_html || '').trim();
const plainExcerpt  = (data.plagiarized_excerpt || '').trim();
const hasHighlight  = highlightHtml.length > 0;

const topBlock = `
  <div class="border rounded-lg bg-rose-50/60 border-rose-200">
    <div class="px-4 py-2 border-b border-rose-200/70 bg-rose-100/70 flex items-center justify-between">
      <div class="text-sm font-semibold text-rose-800">Plagiarized content detected</div>
      <div class="text-xs text-rose-700">
        Max source coverage: <strong>${isNaN(sourceMax) ? '—' : sourceMax + '%'}</strong>

        <span class="text-gray-400">• Doc score: ${isNaN(docAgg) ? '—' : docAgg + '%'}</span>
        ${wordsTotal ? `<span class="text-gray-400">• Words: ${wordsTotal}</span>` : ``}
      </div>
    </div>

    ${hasHighlight ? `
      <!-- tab header -->
      <div class="px-4 pt-3 flex items-center gap-2 text-sm">
        <button type="button" id="tabExact"
          class="px-2.5 py-1 rounded-md bg-blue-600 text-white hover:bg-blue-700">Exact overlaps</button>
        <button type="button" id="tabPlain"
          class="px-2.5 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Plain excerpt</button>
      </div>
    ` : ``}

    <div class="p-4">
      ${hasHighlight ? `
        <!-- highlighted HTML (already script-stripped server-side) -->
         <iframe id="panelExactFrame"
         class="w-full rounded border bg-white"
        style="height: 20rem; border: 1px solid #e5e7eb;"></iframe>
        <pre id="panelPlain" class="whitespace-pre-wrap text-sm leading-relaxed text-gray-900 hidden"
             style="max-height: 20rem; overflow:auto;">${esc(plainExcerpt || '(no crawled text available)')}</pre>
      ` : `
        <pre class="whitespace-pre-wrap text-sm leading-relaxed text-gray-900">${esc(plainExcerpt || '(no crawled text available)')}</pre>
      `}
    </div>
  </div>
`;

    window.__externalScore = Number.isFinite(sourceMax) && sourceMax > 0
      ? sourceMax
      : (Number.isFinite(docAgg) ? docAgg : null);
    updatePlagGate();
    bodyBox.innerHTML = `
      <div class="space-y-4">
    ${renderPhaseBar(data)}
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold">External Plagiarism (Copyleaks)</h3>
      <div class="flex items-center gap-2">
        <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 hover:bg-gray-50"
                id="btnRefreshExternal">Refresh</button>
        <button type="button" class="px-3 py-1.5 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                id="btnRescanExternal">Rescan</button>
      </div>
    </div>

    ${topBlock}

    <!-- Cards -->
    <div class="space-y-3">
      ${renderCards(matches) || `<div class="p-4 rounded bg-gray-50 text-gray-700">No external matches to show.</div>`}
    </div>
  </div>
    `;

if (hasHighlight) {
  const frame = document.getElementById('panelExactFrame');
  const btnExact = document.getElementById('tabExact');
  const btnPlain = document.getElementById('tabPlain');
  const panelPlain = document.getElementById('panelPlain');

  // inject sanitized HTML into the iframe document
  if (frame) {
    const idoc = frame.contentDocument || frame.contentWindow?.document;
    if (idoc) {
      idoc.open();
idoc.write(highlightHtml);   // it's already a complete sanitized HTML doc
idoc.close();

    }
  }

  function showExact() {
    frame?.classList.remove('hidden');
    panelPlain.classList.add('hidden');
    btnExact.classList.add('bg-blue-600','text-white');
    btnExact.classList.remove('border','border-gray-300','text-gray-700','bg-white');
    btnPlain.classList.remove('bg-blue-600','text-white');
    btnPlain.classList.add('border','border-gray-300','text-gray-700','bg-white');
  }
  function showPlain() {
    frame?.classList.add('hidden');
    panelPlain.classList.remove('hidden');
    btnPlain.classList.add('bg-blue-600','text-white');
    btnPlain.classList.remove('border','border-gray-300','text-gray-700','bg-white');
    btnExact.classList.remove('bg-blue-600','text-white');
    btnExact.classList.add('border','border-gray-300','text-gray-700','bg-white');
  }

  btnExact?.addEventListener('click', showExact);
  btnPlain?.addEventListener('click', showPlain);

  // default tab
  showExact();
}



    document.getElementById('btnRefreshExternal')?.addEventListener('click', openAndLoadLatest);
    document.getElementById('btnRescanExternal')?.addEventListener('click', startExternalScan);
  }

  function renderCards(matches) {
    return matches.map(m => {
      const pct   = Number(m.percent || 0);
      const title = (m.source_title || '(untitled)').trim();
      const url   = (m.source_url || '').trim();
      const urlDisp = url ? url : '';

      return `
        <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
          <div class="p-3 flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="text-sm font-medium text-gray-800">
                ${url ? `<a href="${esc(url)}" target="_blank" rel="noopener" class="hover:underline">${esc(title)}</a>` : esc(title)}
              </div>
              ${urlDisp ? `<div class="text-xs text-blue-600 truncate"><a href="${esc(url)}" target="_blank" rel="noopener">${esc(urlDisp)}</a></div>` : ``}
            </div>
            <div class="text-sm font-semibold text-gray-700 shrink-0">${isNaN(pct)?'—':pct+'%'}</div>
          </div>
        </div>
      `;
    }).join('');
  }

 async function startExternalScan() {
    resetPhaseTracker();  
  const el = document.querySelector('.ck-content');
  if (!el) { alert('Editor not ready.'); return; }

  // Initial running view with unknown progress
  renderRunning({ phase:'queued', step_index:1, step_total:6, progress_percent:0 });

  try {
    const resp = await fetch(`{{ route('documents.copyleaks.start') }}`, {
      method: 'POST',
      headers: {
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':'{{ csrf_token() }}',
        'Cache-Control':'no-cache, no-store'
      },
      cache: 'no-store',
      body: JSON.stringify({
        document_id: {{ $document->id }},
        content_html: el.innerHTML
      })
    });
    const start = await resp.json();
    if (!resp.ok || !start?.ok) throw new Error(start?.message || 'Failed to start');

    // Poll ONLY the status endpoint and update the current view
    stopPolling();
    const statusUrl = `{{ route('documents.copyleaks.status', $document) }}`;
    pollingTimer = setInterval(async () => {
      try {
        const next = await getJsonNoCache(statusUrl);
        if (!next || next.status === 'error') {
          stopPolling();
          renderIdle(next?.error || 'Scan failed. Try re-running.');
          return;
        }
        if (next.status === 'running' || next.status === 'queued') {
          renderRunning(next); // update bars/counts in place
          return;
        }
        // Terminal states
        if (next.status === 'completed' || next.status === 'exported') {
          stopPolling();
          renderResults(next);
        }
      } catch {
        // transient network error: keep polling
      }
    }, 3000);
  } catch (e) {
    renderIdle('Failed to start external scan. Please try again.');
  }
}


})();
</script>









    <style>
        .ck-content {
            min-height: 600px;
            background-color: white;
            color: #000;
            padding: 1.5rem !important;
        }
        .ck-content ul, .ck-content ol {
            padding-left: 2rem;
            list-style: disc;
        }
        .ck-content ol {
            list-style: decimal;
        }
        .ck-content li {
            margin-bottom: 0.3em;
        }
        .ck-powered-by {
            display: none !important;
        }
        .ck.ck-toolbar {
            position: sticky !important;
            top: 6rem;
            z-index: 100;
            background-color: white;
        }
        
    </style>
</x-userlayout>
 
