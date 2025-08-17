<x-userlayout>  
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl text-white font-semibold mb-1">
            Review Chapter — {{ $document->chapter ?? 'Untitled Chapter' }}
        </h2>
        <p class="text-blue-100">
            Title: <span class="font-semibold">{{ $document->titleRelation->title ?? 'Untitled Title' }}</span>
        </p>
    </div>

    <div class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-2 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 px-4 py-2 border border-rose-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left: Document Display -->
            <div class="main-container w-full lg:w-2/3">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="mb-6">
                            <label class="block mb-2 font-bold text-blue-600">Title</label>
                            <input 
                                type="text" 
                                value="{{ $document->titleRelation->title }}" 
                                disabled 
                                class="w-full bg-gray-100 border border-gray-300 rounded-md px-4 py-3 text-lg"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block mb-2 font-bold text-blue-600">Document Content</label>
                            <div class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-word-count editor-container_include-fullscreen" id="viewer-container">
                                <div class="editor-container__editor">
                                    <div class="ck-content w-full min-h-[600px] bg-white border border-gray-300 rounded-md shadow-sm leading-relaxed text-base">
                                        {!! $document->content !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Sidebar -->
            <div class="w-full lg:w-1/3 lg:sticky lg:top-6 h-fit">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8">
                    <!-- Document Info -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-700">Document Info</h3>
                        <div class="text-sm text-gray-500 space-y-1">
                            <p><strong>Student:</strong> {{ $document->user->name }}</p>
                            <p><strong>Chapter:</strong> {{ $document->chapter ?? '—' }}</p>
                            <p><strong>Updated on:</strong> {{ $document->updated_at?->format('F d, Y h:i A') ?? '—' }}</p>
                            <p><strong>Authors:</strong> {{ $document->titleRelation->authors ?? '—' }}</p>
                            <p><strong>Research Type:</strong> {{ $document->titleRelation->research_type }}</p>
                            <p><strong>Similarity:</strong> {{ $document->plagiarism_score ?? '—' }}%</p>
                        </div>
                    </div>

                    <!-- Sections Progress -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-700">Sections Progress</h3>
                            <div id="editor-word-count" class="text-sm text-gray-500"></div>
                        </div>
                        <div id="sections-outline" class="text-sm text-gray-700 space-y-2">
                            <p class="text-gray-500">Scanning content…</p>
                        </div>
                    </div>

                   <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-700">Your Message to Student</h3>

    @if(!empty($existingNote))
        <div class="text-xs text-gray-500">
            Last updated: {{ $existingNote->updated_at->format('M d, Y h:ia') }}
        </div>
    @endif

    <form method="POST" action="{{ route('adviser.advised.chapter.note.save', [$title, $document]) }}" class="space-y-3">
        @csrf
        <textarea name="message" rows="5" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
            placeholder="Write your feedback here…">{{ old('message', $existingNote->content ?? '') }}</textarea>

        <button type="submit"
                class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
            Send Message
        </button>
    </form>
</div>

                    <!-- Back action only -->
                    <div class="pt-2 border-t border-gray-200">
                        <a href="{{ route('adviser.advised.show', $title) }}"
                           class="w-full inline-flex justify-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg shadow-sm">
                            ← Back to Title
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Styles for viewer only -->
    <link rel="stylesheet" href="{{ asset('assets/editor.css') }}">
    <style>
        .ck-content {
            background-color: white;
            color: #000;
            min-height: 600px;
            font-size: 1rem;
            line-height: 1.75;
            word-wrap: break-word;
            padding: 1.5rem;
        }
        .ck-content ul, .ck-content ol { padding-left: 2rem; }
        .ck-content ul { list-style-type: disc; }
        .ck-content ol { list-style-type: decimal; }
        .ck-content li { margin-bottom: 0.3em; }
        .editor-container__editor { background-color: white; }
        .ck.ck-toolbar, .ck-powered-by { display: none !important; }
    </style>

    <!-- Progress scanner (same logic as editor) -->
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
  { key: 'Statement of the Problem', patterns: [
      /^(statement of )?the problem$/i, /^problems?\s*statement$/i,
      /^general problems?$/i, /^general problem$/i, /^specific problems?$/i
    ]},
  { key: 'Significance of the Study', patterns: [/^significance( of (the )?study)?$/i] },
  { key: 'Scope and Delimitations', patterns: [/^scope( and)? (delimitation|delimitations)$/i] },
  { key: 'Review of Related Literature', patterns: [/^(review|related (studies|literature))(.*)?$/i, /^rrl$/i] },
  { key: 'Methodology', patterns: [/^method(s|ology)?$/i, /^research methodology$/i] },
  { key: 'Results', patterns: [/^results?$/i, /^findings?$/i] },
  { key: 'Discussion', patterns: [/^discussion$/i, /^analysis$/i] },
  { key: 'Conclusion', patterns: [/^conclusion(s)?$/i] },
  { key: 'Recommendations', patterns: [/^recommendation(s)?$/i] },
  { key: 'Abstract', patterns: [/^abstract$/i] },
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

function isTrueHeading(node){ return node && node.nodeType===1 && /^(H1|H2|H3|H4|H5|H6)$/i.test(node.tagName); }
function hasCenterAlign(node){ const s=(node.getAttribute('style')||'').toLowerCase(); return s.includes('text-align:center'); }
function textOnly(node){ return (node?.innerText || '').replace(/\u00a0/g,' ').trim(); }
function isPseudoHeading(node){
  if (!node || node.nodeType!==1 || node.tagName!=='P') return false;
  const t=textOnly(node); if (!t || t.length>120) return false;
  const hasStrong=node.querySelector('strong,b')!==null;
  const looksLikeTitle=/^[A-Z0-9\s\-:()]+$/.test(t) || hasStrong || hasCenterAlign(node);
  const isKnown=SECTION_ALIASES.some(({patterns})=>patterns.some(rx=>rx.test(t)));
  return looksLikeTitle && isKnown;
}
const TITLE_PAGE_HINTS=[/a research presented/i,/in partial fulfillment/i,/institute of/i,/holy cross college/i,/submitted by/i,/sta\.?\s*ana/i,/^_{3,}$/i,/^[—_]+$/i];
function isFrontMatter(node){
  const t=textOnly(node); if (!t) return true;
  if (node.querySelector('img')) return true;
  if (hasCenterAlign(node)) return true;
  if (TITLE_PAGE_HINTS.some(rx=>rx.test(t))) return true;
  if (/^[A-Z0-9\s_.—-]+$/.test(t) && t.length<=80) return true;
  return false;
}
function countWords(s){ const tokens=s.match(/\b[\p{L}\p{N}’'-]+\b/gu); return tokens?tokens.length:0; }

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
          if (next && isPseudoHeading(next) && /^[A-Z][A-Z\s\-:&]+$/.test(t2)){ title = `${title} — ${t2}`; i++; }
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
        if (next && isPseudoHeading(next) && /^[A-Z][A-Z\s\-:&]+$/.test(t2)){ title = `${title} — ${t2}`; i++; }
      }
      startSection(title); i++; continue;
    }
    addTextFrom(node); i++;
  }

  return sections.filter(s=>s.words>0);
}

function combineSameNamedSections(sections){
  const map=new Map();
  for (const s of sections){
    const prev=map.get(s.name);
    if (prev) prev.words += s.words;
    else map.set(s.name, {...s});
  }
  return Array.from(map.values());
}

function renderSectionsOutline(){
  const box = document.getElementById('sections-outline');
  const wordBox = document.getElementById('editor-word-count');
  if (!box) return;

  const HIDE = [/^chapter\s+/i];
  const raw = extractSections().filter(s => !HIDE.some(rx => rx.test(s.name)));

  const totalWords = countWords(document.querySelector('.ck-content')?.innerText || '');
  if (wordBox) wordBox.textContent = totalWords ? `${totalWords.toLocaleString()} words` : '';

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
          <span class="relative inline-block w-4.5 h-4.5 rounded-full"
                style="background: conic-gradient(#2563eb ${deg}deg, #e5e7eb 0deg); transform: rotate(180deg);">
            <span class="absolute inset-[3px] bg-white rounded-full"></span>
          </span>
          <span class="truncate">${s.name}</span>
        </div>
        <span class="font-medium">${pct}%</span>
      </div>`;
  }).join('');

  box.innerHTML = `<div class="space-y-1">${items || '<p class="text-gray-500">No targeted sections yet.</p>'}</div>`;
}

/** Live updates */
document.addEventListener('DOMContentLoaded', ()=>{
  const wait=setInterval(()=>{
    const el=document.querySelector('.ck-content');
    if (el){
      clearInterval(wait);
      renderSectionsOutline();
      let debounce;
      const mo=new MutationObserver(()=>{
        clearTimeout(debounce);
        debounce=setTimeout(renderSectionsOutline,300);
      });
      mo.observe(el,{subtree:true,childList:true,characterData:true,attributes:true});
    }
  },150);
});
    </script>
</x-userlayout>
