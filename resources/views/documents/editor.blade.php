 <!-- documents/editor.blade.php -->
<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl text-white font-semibold mb-4">
            {{ isset($document) ? 'Edit' : 'New' }} Document — {{ $document->chapter ?? 'Untitled Chapter' }}
        </h2>
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
  </div>

  <div class="flex gap-3">
   

    <div id="plagiarism-result" class="text-sm text-gray-700 hidden"></div>
     <button type="button" id="btnViewMatches"
      class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
      View Matches
    </button>
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
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-2">
           
            <pre class="whitespace-pre-wrap text-sm text-gray-800">{{ $adviserNote->content }}</pre>
        </div>
    @else
        <div class="rounded-lg border border-dashed border-gray-200 p-4 text-sm text-gray-500">
            No adviser note yet for this chapter.
        </div>
    @endif
</div>





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
                    Submit Final Document
                </button>
            </div>
           </div>


  



        </form>
    </div>

</div>
<!-- Offcanvas: Plagiarism Matches -->
<div id="plagOffcanvas" class="fixed inset-0 z-[999] hidden">
  <!-- dim -->
  <div id="plagDim" class="absolute inset-0 bg-black/40"></div>

  <!-- panel -->
  <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white shadow-xl flex flex-col">
    <div class="p-4 border-b flex items-center justify-between">
      <h3 class="text-lg font-semibold">Plagiarism Matches (from Chapter 1 onward)</h3>
      <button id="plagClose" class="p-2 rounded hover:bg-gray-100" aria-label="Close">✕</button>
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
            <div class="p-4 rounded border bg-gray-50 text-gray-700">No matches found for the current settings.</div>
          </div>`;
        return;
      }

      const cards = matches.map(m => `
        <div class="rounded-xl border border-gray-200 overflow-hidden mb-4">
          <!-- header -->
          <div class="px-4 py-2 bg-gray-50 flex items-center justify-between">
            <div class="text-sm text-gray-700">
              <span class="font-semibold">Similarity:</span> ${m.percent}%
            </div>
            <div class="text-xs text-gray-500">${m.source_chapter ?? ''}</div>
          </div>

          <!-- row 1: YOUR content -->
          <div class="p-4">
            <div class="text-xs font-semibold text-gray-500 mb-1">Your content</div>
            <pre class="whitespace-pre-wrap text-sm leading-relaxed text-gray-800">${escapeHtml(m.your_excerpt)}</pre>
          </div>

          <hr class="border-gray-100">

          <!-- row 2: SOURCE -->
          <div class="p-4">
            <div class="text-xs font-semibold text-gray-500 mb-1">
              Source: <span class="text-gray-800">${escapeHtml(m.source_title)}</span>
            </div>
            <pre class="whitespace-pre-wrap text-sm leading-relaxed text-gray-800">${escapeHtml(m.source_excerpt)}</pre>
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
// ---------- config ----------
const THRESHOLD = 45;
const MIN_CHARS_TO_CHECK = 40; // avoid noise when the doc is still empty
const DISPLAY_MIN = 0;
const BLOCK_THRESHOLD = 45;  
// ---------- helpers ----------
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
  if (reason && resultBox) {
    resultBox.classList.remove('hidden');
    resultBox.innerHTML = `<span class="text-gray-600">${reason}</span>`;
  }
}

// Use the same source the offcanvas uses (HTML) so cleaning & chapter-skip match
function getEditorHTML() {
  const el = document.querySelector('.ck-content');
  return el ? el.innerHTML.trim() : null;
}
// For a quick length gate we can still look at the visible text:
function getEditorVisibleTextLength() {
  const el = document.querySelector('.ck-content');
  return el ? el.innerText.trim().length : 0;
}

// ---------- run check ----------
async function checkPlagiarism() {
  const html = getEditorHTML();
  if (html === null) {
    setButtonsDisabled(true, 'Editor not ready yet…');
    return;
  }
  if (getEditorVisibleTextLength() < MIN_CHARS_TO_CHECK) {
    setButtonsDisabled(true, 'Type more content, then run the checker.');
    resultBox.classList.remove('hidden');
    resultBox.innerHTML = `<span class="text-gray-600">Waiting for more content…</span>`;
    return;
  }

  resultBox.classList.remove('hidden');
  resultBox.innerHTML = `
    <div class="flex items-center space-x-2 text-gray-600">
      <svg class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
      <span>Checking for plagiarism...</span>
    </div>
  `;

  try {
    const response = await fetch("{{ route('documents.checkPlagiarismLive') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        content_html: html,                 // ← IMPORTANT: send HTML (same as offcanvas)
        document_id: {{ $document->id }},
        min_percent: DISPLAY_MIN 
      })
    });

    const data  = await response.json();
    const score = Number(data.score ?? 0);

    resultBox.innerHTML = `<span>Plagiarism Score:</span> ${score}%`;

    if (score >= BLOCK_THRESHOLD) {
      resultBox.innerHTML += `<br><span class="text-red-600">High similarity detected! ⚠️</span>`;
      setButtonsDisabled(true);
    } else {
      resultBox.innerHTML += `<br><span class="text-green-600">Content appears original ✅</span>`;
      setButtonsDisabled(false);
    }
  } catch (error) {
    setButtonsDisabled(true, 'Error checking plagiarism. Please try again.');
  }
}

// ---------- init: disable on load, then auto-run when editor is ready ----------
document.addEventListener('DOMContentLoaded', () => {
  setButtonsDisabled(true, 'Run the plagiarism checker to enable Save & Submit.');

  // Wait for CKEditor .ck-content to exist, then auto-run once
  const waitForEditor = setInterval(() => {
    const el = document.querySelector('.ck-content');
    if (el) {
      clearInterval(waitForEditor);
      setTimeout(checkPlagiarism, 300);

      // Re-disable when user types; debounce re-check
      let debounce;
      const observer = new MutationObserver(() => {
        setButtonsDisabled(true, 'Content changed. Please run the checker again.');
        clearTimeout(debounce);
        // Auto re-check after user stops typing for 1s
        debounce = setTimeout(checkPlagiarism, 1000);
      });
      observer.observe(el, { subtree: true, characterData: true, childList: true });
    }
  }, 150);
});

// Expose to the Run button
window.checkPlagiarism = checkPlagiarism;
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
 
