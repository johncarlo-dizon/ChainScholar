<!-- resources/views/pdfconverter/index.blade.php -->
<x-userlayout>

    {{-- ===== Alerts (success / error) ===== --}}
    @if(session('success'))
        <div id="success-alert" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                <button type="button" class="ml-auto inline-flex rounded-md p-1.5 text-green-600/80 hover:bg-green-100" data-dismiss="alert" aria-label="Dismiss">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div id="error-alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-red-800">{!! session('error') !!}</p>
                <button type="button" class="ml-auto inline-flex rounded-md p-1.5 text-red-600/80 hover:bg-red-100" data-dismiss="alert" aria-label="Dismiss">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- ===== Page header ===== --}}
    <div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 p-6 shadow">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-semibold text-white">Research Paper Submission</h2>
                <p class="text-white/90 mt-1 text-sm">Upload your PDF, complete the metadata, and pass the plagiarism threshold to submit.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="hidden md:flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-white">
                    <span class="text-xs opacity-90">Status:</span>
                    <span id="header-status-chip" class="rounded-full bg-white/20 px-2 py-0.5 text-xs">Waiting for PDF</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Content area: two-column on desktop, single-column on mobile ===== --}}
    <div class="mx-auto mt-5 grid max-w-7xl grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- ===== Left: Form (spans 2 cols on desktop) ===== --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <form action="{{ route('research-papers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 p-6 md:p-8" id="pdf-upload-form">
                    @csrf
                    <input type="hidden" name="plagiarism_score" id="plagiarism_score" value="">

                    {{-- Upload card (drag & drop) --}}
                    <section aria-labelledby="upload-section">
                        <h3 id="upload-section" class="text-base font-semibold text-gray-800">Upload PDF</h3>
                        <p class="mt-1 text-sm text-gray-500">Drag & drop your file here or click to choose. Only <strong>.pdf</strong> is allowed.</p>

                        <label
                            for="pdfFile"
                            class="mt-3 block cursor-pointer rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/60 p-6 transition hover:border-indigo-300 hover:bg-indigo-50/40 focus:outline-none"
                            id="drop-area"
                        >
                            <div class="flex flex-col items-center justify-center gap-3 text-center">
                                <svg class="h-10 w-10 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium text-gray-700">Choose file or drop it here</span>
                                    <span class="text-xs text-gray-500">Max size depends on server limits</span>
                                </div>
                                <span class="inline-flex items-center rounded-md bg-white px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-200">Browse PDF</span>
                            </div>
                            <input id="pdfFile" name="fileToUpload" type="file" class="sr-only" accept=".pdf" required>
                        </label>

                        {{-- Selected file pill + warnings --}}
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <span id="file-name" class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M4 4a2 2 0 012-2h5l5 5v9a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                                </svg>
                                No file selected
                            </span>
                            <p id="filename-warning" class="text-xs font-medium text-red-600"></p>
                        </div>
                    </section>

                    {{-- Basic metadata --}}
                    <section aria-labelledby="meta-section">
                        <h3 id="meta-section" class="text-base font-semibold text-gray-800">Paper Details</h3>
                        <div class="mt-3 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-1">
                                <label for="pdfTitle" class="text-sm font-medium text-gray-700">Title</label>
                                <input type="text" id="pdfTitle" name="title" placeholder="Research paper title" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="space-y-1">
                                <label for="yearInput" class="text-sm font-medium text-gray-700">Year</label>
                                <input type="number" id="yearInput" name="year" placeholder="2025" min="1900" max="2099" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="mt-6 space-y-1">
                            <label for="authorInput" class="text-sm font-medium text-gray-700">Author(s)</label>
                            <input type="text" id="authorInput" name="authors" placeholder="John Doe, Jane Smith" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Separate multiple authors with commas</p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-1">
                                <label for="departmentSelect" class="text-sm font-medium text-gray-700">Department</label>
                                <select id="departmentSelect" name="department" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="" disabled selected>Select department</option>
                                    <option value="Senior High School">Senior High School</option>
                                    <option value="School of Computing, Information Technology and Engineering">School of Computing, IT and Engineering</option>
                                    <option value="School of Arts, Sciences, and Education">School of Arts, Sciences, and Education</option>
                                    <option value="School of Criminal Justice">School of Criminal Justice</option>
                                    <option value="School of Tourism and Hospitality Management">School of Tourism and Hospitality</option>
                                    <option value="School of Business and Accountancy">School of Business and Accountancy</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label for="categorySelect" class="text-sm font-medium text-gray-700">Program</label>
                                <select id="categorySelect" name="program" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="" disabled selected>Select department first</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 space-y-1">
                            <label for="abstract" class="text-sm font-medium text-gray-700">Abstract</label>
                            <textarea id="abstract" name="abstract" rows="4" placeholder="Enter your research abstract..." required
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        {{-- Extracted text (hidden helper) --}}
                        <div class="hidden">
                            <label for="pdfText" class="block text-sm font-medium text-gray-700">Extracted Text</label>
                            <textarea id="pdfText" name="ocrPdf" rows="8" readonly
                                      class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </section>

                    {{-- Submit --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <span class="text-xs text-gray-500">Submit enabled after passing plagiarism threshold</span>
                        <button type="submit" id="submitBtn"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                            Submit Research
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== Right: Sticky plagiarism panel ===== --}}
        <aside class="lg:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm lg:sticky lg:top-20">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-semibold text-gray-800">Plagiarism Checker</h3>
                        <span
                            id="pdf-plag-badge"
                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700"
                            title="Shows the current similarity score"
                        >—</span>
                    </div>
                    <button type="button" id="btnPdfViewMatches"
                            class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700">
                        View Matches
                    </button>
                </div>
                <div class="px-5 py-4">
                    <div id="pdf-plagiarism-result" class="prose prose-sm max-w-none text-gray-700"></div>
                    <div class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">
                        Tip: For best results, ensure your PDF contains selectable text (not only images).
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- ===== Off-canvas: PDF Plagiarism Matches ===== --}}
    <div id="pdfPlagOffcanvas" class="fixed inset-0 z-[999] hidden">
        <div id="pdfPlagDim" class="absolute inset-0 bg-black/10"></div>
        <div class="absolute right-0 top-0 flex h-full w-full max-w-2xl flex-col bg-white shadow">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                <h3 class="text-lg font-semibold">Plagiarism Matches (PDF)</h3>
                <button id="pdfPlagClose" class="rounded p-2 hover:bg-gray-100" aria-label="Close">✕</button>
            </div>
            <div id="pdfPlagBody" class="grow overflow-y-auto p-4"></div>
        </div>
    </div>

    {{-- ===== pdf.js ===== --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.9.179/pdf.min.js"></script>

    {{-- ===== Dep/Program linkage ===== --}}
    <script>
        const programs = {
            "Senior High School": [
                "Accountancy, Business, and Management",
                "Science, Technology, Engineering, and Mathematics",
                "Humanities and Social Sciences",
                "General Academic Strand",
                "Technical-Vocational-Livelihood - Home Economics",
                "Technical-Vocational-Livelihood - Information and Communications Technology"
            ],
            "School of Computing, Information Technology and Engineering": [
                "Bachelor of Science in Civil Engineering",
                "Bachelor of Science in Computer Engineering",
                "Bachelor of Science in Computer Science",
                "Bachelor of Science in Information Technology",
                "Bachelor of Library and Information Science"
            ],
            "School of Arts, Sciences, and Education": [
                "Bachelor of Elementary Education",
                "Bachelor of Science in Development Communication",
                "Bachelor of Science in Psychology",
                "Bachelor of Secondary Education major in English",
                "Bachelor of Secondary Education major in Filipino",
                "Bachelor of Secondary Education major in Mathematics",
                "Bachelor of Secondary Education major in Science"
            ],
            "School of Criminal Justice": ["Bachelor of Science in Criminology"],
            "School of Tourism and Hospitality Management": [
                "Bachelor of Science in Hospitality Management",
                "Bachelor of Science in Tourism Management"
            ],
            "School of Business and Accountancy": [
                "Bachelor of Science in Accountancy",
                "Bachelor of Science in Accounting Information System",
                "Bachelor of Science in Business Administration major in Financial Management",
                "Bachelor of Science in Business Administration major in Marketing Management"
            ]
        };
        const departmentSelect = document.getElementById('departmentSelect');
        const categorySelect   = document.getElementById('categorySelect');
        departmentSelect.addEventListener('change', function () {
            const options = programs[this.value] || [];
            categorySelect.innerHTML = '<option selected disabled value="">Choose...</option>';
            options.forEach(program => {
                const option = document.createElement('option');
                option.value = program;
                option.textContent = program;
                categorySelect.appendChild(option);
            });
        });
    </script>

    {{-- ===== PDF extraction + filename validation ===== --}}
    <script>
        const pdfFileInput    = document.getElementById('pdfFile');
        const pdfTextArea     = document.getElementById('pdfText');
        const submitBtn       = document.getElementById('submitBtn');
        const filenameWarning = document.getElementById('filename-warning');
        const fileNameEl      = document.getElementById('file-name');
        const headerStatus    = document.getElementById('header-status-chip');
        const dropArea        = document.getElementById('drop-area');

        // Alert dismiss
        document.querySelectorAll('[data-dismiss="alert"]').forEach(btn=>{
            btn.addEventListener('click', ()=> btn.closest('[id$="-alert"]')?.remove());
        });

        // Drag & drop styling
        ;['dragenter','dragover'].forEach(evt=>{
            dropArea.addEventListener(evt, e=>{
                e.preventDefault(); e.stopPropagation();
                dropArea.classList.add('ring-2','ring-indigo-300');
            });
        });
        ;['dragleave','drop'].forEach(evt=>{
            dropArea.addEventListener(evt, e=>{
                e.preventDefault(); e.stopPropagation();
                dropArea.classList.remove('ring-2','ring-indigo-300');
            });
        });
        dropArea.addEventListener('drop', e=>{
            const file = e.dataTransfer?.files?.[0];
            if (file) {
                pdfFileInput.files = e.dataTransfer.files;
                pdfFileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        if (typeof pdfjsLib !== 'undefined') {
            pdfFileInput.addEventListener('change', async (event) => {
                filenameWarning.textContent = '';
                submitBtn.disabled = true;
                pdfTextArea.value = '';
                headerStatus.textContent = 'Reading PDF…';

                const file = event.target.files[0];
                fileNameEl.textContent = file?.name || 'No file selected';
                if (!file) return;

                // Duplicate filename check
                try {
                    const response = await fetch(`{{ route('research-papers.check-filename') }}?filename=${encodeURIComponent(file.name)}`);
                    const data = await response.json();
                    if (data.exists) {
                        filenameWarning.textContent = `You already have a file named "${file.name}". Please rename your file.`;
                        submitBtn.disabled = true;
                        pdfFileInput.value = '';
                        headerStatus.textContent = 'Waiting for PDF';
                        return;
                    }
                } catch (_) {}

                if (file.type !== 'application/pdf') {
                    filenameWarning.textContent = 'Invalid file type. Only PDF is allowed.';
                    submitBtn.disabled = true;
                    headerStatus.textContent = 'Waiting for PDF';
                    return;
                }

                // Client-side preview extraction (optional)
                try {
                    const arrayBuffer = await file.arrayBuffer();
                    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                    let fullText = '';
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);
                        const textContent = await page.getTextContent();
                        const pageText = textContent.items.map(item => item.str).join(' ');
                        fullText += pageText + '\n\n';
                    }
                    pdfTextArea.value = fullText;
                } catch (error) {
                    console.warn('pdf.js extraction failed (server will handle):', error);
                    pdfTextArea.value = '';
                }

                // Always run authoritative server check
                if (window.runLivePdfPlagiarismCheck) {
                    headerStatus.textContent = 'Checking plagiarism…';
                    window.runLivePdfPlagiarismCheck().finally(()=>{
                        // status chip updated by checker via submit enable/disable visual
                    });
                }
            });
        } else {
            console.error("pdf.js is not loaded.");
        }
    </script>

    {{-- ===== Plagiarism UI + logic (file-first) ===== --}}
    <script>
        (function(){
            const resultBox  = document.getElementById('pdf-plagiarism-result');
            const submitBtn  = document.getElementById('submitBtn');
            const textArea   = document.getElementById('pdfText');
            const viewBtn    = document.getElementById('btnPdfViewMatches');
            const badge      = document.getElementById('pdf-plag-badge');
            const scoreInput = document.getElementById('plagiarism_score');
            const headerChip = document.getElementById('header-status-chip');

            const offcanvas  = document.getElementById('pdfPlagOffcanvas');
            const dim        = document.getElementById('pdfPlagDim');
            const closeBtn   = document.getElementById('pdfPlagClose');
            const bodyBox    = document.getElementById('pdfPlagBody');

            const BLOCK_THRESHOLD = 45;
            let lastScore = null;

            function setSubmitDisabled(disabled){
                submitBtn.disabled = disabled;
                submitBtn.classList.toggle('opacity-50', disabled);
                submitBtn.classList.toggle('cursor-not-allowed', disabled);
            }

            function setBadge(score){
                lastScore = score;
                scoreInput.value = isNaN(Number(score)) ? '' : String(score);
                if (score === '—' || isNaN(Number(score))) {
                    badge.textContent = '—';
                    badge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700';
                    headerChip.textContent = 'Waiting for PDF';
                    return;
                }
                const blocked = Number(score) >= BLOCK_THRESHOLD;
                badge.textContent = `${score}%`;
                badge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ' +
                    (blocked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700');
                headerChip.textContent = blocked ? 'Blocked (High Similarity)' : 'Ready to Submit';
            }

            async function runLivePdfPlagiarismCheck(){
                const fileInput = document.getElementById('pdfFile');
                const file = fileInput?.files?.[0] || null;
                const txt  = (textArea.value || '').trim();

                if (!file && !txt){
                    resultBox.innerHTML = '<span class="text-gray-600">Waiting for PDF…</span>';
                    setBadge('—');
                    setSubmitDisabled(true);
                    return;
                }

                resultBox.innerHTML = `
                  <div class="flex items-center gap-2 text-gray-600">
                    <svg class="h-5 w-5 animate-spin text-red-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                      <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
                    </svg>
                    <span>Checking for plagiarism...</span>
                  </div>`;

                try{
                    let score = 0;

                    if (file) {
                        const fd = new FormData();
                        fd.append('file', file);
                        fd.append('_token', '{{ csrf_token() }}');

                        const res = await fetch("{{ route('research-papers.check-plagiarism') }}", {
                            method: 'POST',
                            body: fd
                        });
                        const data = await res.json();
                        score = Number(data.score ?? 0);
                    } else {
                        const res = await fetch("{{ route('research-papers.check-plagiarism') }}", {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                            body: JSON.stringify({ pdf_text: txt })
                        });
                        const data = await res.json();
                        score = Number(data.score ?? 0);
                    }

                    setBadge(score);

                    let html = `<div class="text-sm">Plagiarism Score: <strong>${score}%</strong></div>`;
                    if (score >= BLOCK_THRESHOLD) {
                        html += `<div class="mt-1 text-sm text-red-600">High similarity detected! ⚠️ Upload disabled.</div>`;
                        setSubmitDisabled(true);
                    } else {
                        html += `<div class="mt-1 text-sm text-green-600">Content appears original ✅</div>`;
                        setSubmitDisabled(false);
                    }
                    resultBox.innerHTML = html;
                }catch(err){
                    console.error(err);
                    setBadge('—');
                    setSubmitDisabled(true);
                    headerChip.textContent = 'Error';
                    resultBox.innerHTML = '<span class="text-red-600">Error checking plagiarism. Please try again.</span>';
                }
            }

            // expose to file-change handler
            window.runLivePdfPlagiarismCheck = runLivePdfPlagiarismCheck;

            // Guard submit
            document.getElementById('pdf-upload-form').addEventListener('submit', (e)=>{
                if (lastScore !== null && Number(lastScore) >= BLOCK_THRESHOLD){
                    e.preventDefault();
                    resultBox.innerHTML += `<div class="mt-2 text-sm text-red-600">Submission blocked due to high similarity (${lastScore}%).</div>`;
                }
            });

            // Off-canvas handlers
            function esc(s){ return (s ?? '').toString().replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }
            function openOff(){ offcanvas.classList.remove('hidden'); }
            function closeOff(){ offcanvas.classList.add('hidden'); }
            dim?.addEventListener('click', closeOff);
            closeBtn?.addEventListener('click', closeOff);

            document.getElementById('btnPdfViewMatches')?.addEventListener('click', async ()=>{
                const fileInput = document.getElementById('pdfFile');
                const file = fileInput?.files?.[0] || null;
                const txt  = (document.getElementById('pdfText').value || '').trim();

                openOff();
                bodyBox.innerHTML = `
                  <div class="flex items-center gap-2 text-gray-600">
                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                      <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
                    </svg>
                    <span>Scanning for detailed matches…</span>
                  </div>`;

                try{
                    let data;
                    if (file){
                        const fd = new FormData();
                        fd.append('file', file);
                        fd.append('_token', '{{ csrf_token() }}');

                        const res = await fetch("{{ route('research-papers.check-plagiarism-detailed') }}", {
                            method: 'POST',
                            body: fd
                        });
                        data = await res.json();
                    } else {
                        const res = await fetch("{{ route('research-papers.check-plagiarism-detailed') }}", {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                            body: JSON.stringify({ pdf_text: txt })
                        });
                        data = await res.json();
                    }

                    const matches = Array.isArray(data.matches) ? data.matches : [];
                    const score   = Number(data.score ?? 0);

                    if(!matches.length){
                        bodyBox.innerHTML = `
                          <div class="space-y-3">
                            <div class="text-sm text-gray-600">Overall Score: <strong>${score}%</strong></div>
                            <div class="rounded-lg bg-gray-50 p-4 text-gray-700">No matches found for the current settings.</div>
                          </div>`;
                        return;
                    }

                    const cards = matches.map(m => `
                      <div class="mb-4 overflow-hidden rounded-xl border border-gray-200">
                        <div class="flex items-center justify-between bg-gray-50 px-4 py-2">
                          <div class="text-sm text-gray-700"><span class="font-semibold">Similarity:</span> ${m.percent}%</div>
                        </div>
                        <div class="p-4">
                          <div class="mb-1 text-xs font-semibold text-gray-500">Your content</div>
                          <pre class="whitespace-pre-wrap text-sm leading-relaxed text-gray-800">${esc(m.your_excerpt)}</pre>
                        </div>
                        <hr class="border-gray-100">
                        <div class="p-4">
                          <div class="mb-1 text-xs font-semibold text-gray-500">
                            Source: <span class="text-gray-800">${esc(m.source_title)}</span>
                          </div>
                          <pre class="whitespace-pre-wrap text-sm leading-relaxed text-gray-800">${esc(m.source_excerpt)}</pre>
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
                    console.error(err);
                    bodyBox.innerHTML = `<div class="rounded border bg-red-50 p-4 text-red-700">Error generating matches. Please try again.</div>`;
                }
            });

            // Initial state
            document.addEventListener('DOMContentLoaded', () => {
                setSubmitDisabled(true);
                badge.textContent = '—';
                headerChip.textContent = 'Waiting for PDF';
            });
        })();
    </script>

</x-userlayout>
