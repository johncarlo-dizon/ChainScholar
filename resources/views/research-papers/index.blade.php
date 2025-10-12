<!-- resources/views/pdfconverter/index.blade.php -->
<x-userlayout>

    @if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
        <div class="flex items-start gap-3">
            <svg class="h-5 w-5 text-red-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="text-sm text-red-800">
                <p class="font-medium mb-1">Please fix the following:</p>
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <x-header.bar
        title="Research Paper Submission"
        subtitle="Upload and submit your completed research PDF document"
        :unread-count="$unreadCount ?? 0"
        :notifications="$notifications ?? collect()"
        :user="Auth::user()"
    />

    {{-- ===== Content area: two-column on desktop, single-column on mobile ===== --}}
    <div class="mx-auto mt-5 grid max-w-8xl grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- ===== Left: Form (spans 2 cols on desktop) ===== --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <form action="{{ route('research-papers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 p-6 md:p-8" id="pdf-upload-form">
                    @csrf
                    <input type="hidden" name="plagiarism_score" id="plagiarism_score" value="">

                   {{-- Upload card (drag & drop) --}}
<section aria-labelledby="upload-section">
    <h3 id="upload-section" class="text-base font-semibold text-gray-800">Upload PDF</h3>
    <p class="mt-1 text-sm text-gray-500">Choose a <strong>.pdf</strong> file with <strong>selectable text</strong> (not scanned images only).</p>

    <div class="mt-3">
        <input
            id="pdfFile"
            name="fileToUpload"
            type="file"
            accept=".pdf,application/pdf"
            required
            class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-indigo-700 rounded-lg shadow-sm cursor-pointer"
        />
    </div>

    @error('fileToUpload')
    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
    @enderror

    {{-- Selected file pill + warnings --}}
    <div class="mt-3">
        <p id="filename-warning" class="text-xs font-medium text-red-600"></p>
    </div>

    {{-- File status indicator --}}
    <div id="file-status" class="mt-3 hidden">
        <div class="flex items-center gap-2">
            <div id="file-status-icon" class="h-4 w-4"></div>
            <span id="file-status-text" class="text-sm"></span>
        </div>
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
                                    <option value="School of Computing, Information Technology and Engineering">School of Computing, Information Technology and Engineering</option>
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
                    </div>

                    <button type="button" id="btnPdfViewMatches"
                        class="rounded-lg bg-gray-400 px-3 py-1.5 text-sm font-medium text-white shadow-sm transition hover:bg-gray-500 disabled:cursor-not-allowed disabled:opacity-50"
                        disabled>
                        View Matches
                    </button>
                </div>
                <div class="px-5 py-4">
                    <div id="pdf-plagiarism-result" class="prose prose-sm max-w-none text-gray-700">
                        <div class="flex flex-col items-center justify-center py-4 text-center">
                            <svg class="h-12 w-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                            <p class="text-sm text-gray-500">Upload a PDF to check for plagiarism</p>
                        </div>
                    </div>
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

    <script>
    /** Safely read JSON. Never throws. */
    async function readJsonSafe(res) {
        try {
            const ct = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                try { return await res.json(); } catch { return {}; }
            }
            return await res.json();
        } catch (_) {
            return {};
        }
    }
    </script>

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

    <script>
    (() => {
        // ==== Element refs ====
        const pdfFileInput    = document.getElementById('pdfFile');
        const pdfTextArea     = document.getElementById('pdfText');
        const submitBtn       = document.getElementById('submitBtn');
        const filenameWarning = document.getElementById('filename-warning');
        const scoreInput      = document.getElementById('plagiarism_score');
        const resultBox       = document.getElementById('pdf-plagiarism-result');
        const viewBtn         = document.getElementById('btnPdfViewMatches');
        const fileStatus      = document.getElementById('file-status');
        const fileStatusIcon  = document.getElementById('file-status-icon');
        const fileStatusText  = document.getElementById('file-status-text');

        // Off-canvas
        const offcanvas  = document.getElementById('pdfPlagOffcanvas');
        const dim        = document.getElementById('pdfPlagDim');
        const closeBtn   = document.getElementById('pdfPlagClose');
        const bodyBox    = document.getElementById('pdfPlagBody');

        // ==== Config ====
        const BLOCK_THRESHOLD = 45;
        let lastScore = null;
        let currentScanData = null; // Store scan results to share between sidebar and offcanvas
        let isScanning = false;
        let currentScanId = null;
        let hasScanCompleted = false;

        // ==== Helpers ====
        function setSubmitDisabled(disabled) {
            if (!submitBtn) return;
            submitBtn.disabled = disabled;
            submitBtn.classList.toggle('opacity-50', disabled);
            submitBtn.classList.toggle('cursor-not-allowed', disabled);
        }

        function setBadge(score) {
            lastScore = score;
            if (scoreInput) {
                scoreInput.value = isNaN(Number(score)) ? '' : String(score);
            }
        }

        function esc(s){
            return (s ?? '').toString()
                .replaceAll('&','&amp;')
                .replaceAll('<','&lt;')
                .replaceAll('>','&gt;');
        }

        function updateViewMatchesButton() {
            if (!viewBtn) return;
            
            // Enable button only if scan is complete and we have matches
            const hasMatches = currentScanData && currentScanData.matches && currentScanData.matches.length > 0;
            const shouldEnable = hasScanCompleted && hasMatches;
            
            viewBtn.disabled = !shouldEnable;
            viewBtn.classList.toggle('bg-red-600', shouldEnable);
            viewBtn.classList.toggle('hover:bg-red-700', shouldEnable);
            viewBtn.classList.toggle('bg-gray-400', !shouldEnable);
            viewBtn.classList.toggle('hover:bg-gray-500', !shouldEnable);
        }

        function updateFileStatus(status, type = 'info') {
            if (!fileStatus || !fileStatusIcon || !fileStatusText) return;
            
            const icons = {
                info: `<svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`,
                success: `<svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`,
                warning: `<svg class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>`,
                error: `<svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`,
                loading: `<svg class="h-4 w-4 animate-spin text-blue-500" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                    <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
                </svg>`
            };
            
            fileStatusIcon.innerHTML = icons[type] || icons.info;
            fileStatusText.textContent = status;
            fileStatus.classList.remove('hidden');
        }

        function resetUI() {
            hasScanCompleted = false;
            currentScanData = null;
            setSubmitDisabled(true);
            updateViewMatchesButton();
            
            resultBox.innerHTML = `
                <div class="flex flex-col items-center justify-center py-4 text-center">
                    <svg class="h-12 w-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                    <p class="text-sm text-gray-500">Upload a PDF to check for plagiarism</p>
                </div>`;
            
            fileStatus.classList.add('hidden');
        }

        // ==== Main Plagiarism Check Function ====
        async function runLivePdfPlagiarismCheck(){
            if (isScanning) {
                console.log('Scan already in progress, aborting duplicate request');
                return;
            }
            
            const fileInput = pdfFileInput;
            const file = fileInput?.files?.[0] || null;
            const txt  = (pdfTextArea?.value || '').trim();

            if (!file && !txt) {
                resetUI();
                return;
            }

            isScanning = true;
            hasScanCompleted = false;
            currentScanId = Date.now();
            const scanId = currentScanId;

            updateFileStatus('Scanning document for plagiarism...', 'loading');
            resultBox.innerHTML = `
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="h-5 w-5 animate-spin text-blue-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                        <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
                    </svg>
                    <span>Checking for plagiarism...</span>
                </div>`;

            try {
                let score = 0;
                let detailedData = null;

                if (file) {
                    const fd = new FormData();
                    fd.append('file', file);
                    fd.append('_token', '{{ csrf_token() }}');

                    // Get basic score first
                    const res = await fetch("{{ route('research-papers.check-plagiarism') }}", { method:'POST', body: fd });
                    const data = await readJsonSafe(res);
                    score = Number(data.score ?? 0);

                    // Get detailed matches for both sidebar and offcanvas
                    const detailedRes = await fetch("{{ route('research-papers.check-plagiarism-detailed') }}", { method:'POST', body: fd });
                    detailedData = await readJsonSafe(detailedRes);
                } else {
                    const res = await fetch("{{ route('research-papers.check-plagiarism') }}", {
                        method:'POST',
                        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                        body: JSON.stringify({ pdf_text: txt })
                    });
                    const data = await readJsonSafe(res);
                    score = Number(data.score ?? 0);

                    const detailedRes = await fetch("{{ route('research-papers.check-plagiarism-detailed') }}", {
                        method:'POST',
                        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                        body: JSON.stringify({ pdf_text: txt })
                    });
                    detailedData = await readJsonSafe(detailedRes);
                }

                // Check if this scan is still relevant
                if (scanId !== currentScanId) {
                    console.log('Scan outdated, ignoring results');
                    return;
                }

                // Store data for both sidebar and offcanvas
                currentScanData = {
                    score: score,
                    matches: detailedData.matches || [],
                    timestamp: Date.now()
                };

                setBadge(score);
                hasScanCompleted = true;

                let html = `<div class="text-sm">Plagiarism Score: <strong>${isNaN(score)?'—':score+'%'}</strong></div>`;
                if (!isNaN(score) && score >= BLOCK_THRESHOLD) {
                    html += `<div class="mt-1 text-sm text-red-600">High similarity detected! ⚠️ Upload disabled.</div>`;
                    setSubmitDisabled(true);
                    updateFileStatus('High plagiarism detected. Please revise your document.', 'error');
                } else {
                    html += `<div class="mt-1 text-sm text-green-600">Content appears original ✅</div>`;
                    setSubmitDisabled(false);
                    updateFileStatus('Document scanned successfully. Ready for submission.', 'success');
                }
                resultBox.innerHTML = html;

                // Update the View Matches button state
                updateViewMatchesButton();

            } catch (err) {
                console.error(err);
                if (scanId === currentScanId) {
                    setBadge('—');
                    setSubmitDisabled(true);
                    resultBox.innerHTML = '<span class="text-red-600">Error checking plagiarism. Please try again.</span>';
                    currentScanData = null;
                    updateFileStatus('Error during plagiarism check. Please try again.', 'error');
                }
            } finally {
                if (scanId === currentScanId) {
                    isScanning = false;
                }
            }
        }
        window.runLivePdfPlagiarismCheck = runLivePdfPlagiarismCheck;

        // ==== File input: extraction + filename validation ====
if (typeof pdfjsLib !== 'undefined' && pdfFileInput) {
    pdfFileInput.addEventListener('change', async (event) => {
        filenameWarning.textContent = '';
        setSubmitDisabled(true);
        if (pdfTextArea) pdfTextArea.value = '';
        currentScanData = null; // Clear previous scan data
        hasScanCompleted = false;
        updateViewMatchesButton();

        const file = event.target.files?.[0] || null;
        if (!file) {
            filenameWarning.textContent = '';
            resetUI();
            return;
        }

        // Reset UI for new file
        updateFileStatus('Validating file...', 'loading');

        // Duplicate filename check
        try {
            const response = await fetch(`{{ route('research-papers.check-filename') }}?filename=${encodeURIComponent(file.name)}`);
            const data = await readJsonSafe(response);
            if (data.exists) {
                filenameWarning.textContent = `You already have a file named "${file.name}". Please rename your file.`;
                setSubmitDisabled(true);
                pdfFileInput.value = '';
                updateFileStatus('Duplicate filename detected.', 'error');
                return;
            }
        } catch (_) { 
            console.warn('Filename check failed');
        }

        if (file.type !== 'application/pdf') {
            filenameWarning.textContent = 'Invalid file type. Only PDF is allowed.';
            setSubmitDisabled(true);
            updateFileStatus('Invalid file type. Please upload a PDF.', 'error');
            return;
        }

        updateFileStatus('Extracting text from PDF...', 'loading');

        // Client-side preview extraction with proper error handling
        try {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            let fullText = '';
            let hasExtractableText = false;
            
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const textContent = await page.getTextContent();
                const pageText = textContent.items.map(item => item.str).join(' ');
                fullText += pageText + '\n\n';
                
                // Check if this page has any extractable text
                if (pageText.trim().length > 0) {
                    hasExtractableText = true;
                }
            }

            // Check if we found any extractable text
            if (!hasExtractableText || fullText.trim().length === 0) {
                filenameWarning.textContent = 'This PDF does not contain extractable text. Please submit a PDF with selectable text (not scanned images only).';
                setSubmitDisabled(true);
                pdfFileInput.value = '';
                updateFileStatus('No extractable text found in PDF.', 'error');
                resultBox.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-4 text-center">
                        <svg class="h-12 w-12 text-red-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <p class="text-sm font-medium text-red-600">Invalid PDF</p>
                        <p class="text-xs text-red-500 mt-1">PDF contains no extractable text</p>
                    </div>`;
                return;
            }

            // Success - we have extractable text
            if (pdfTextArea) pdfTextArea.value = fullText;
            updateFileStatus('Text extracted successfully. Scanning for plagiarism...', 'success');
            
        } catch (error) {
            console.error('PDF text extraction failed:', error);
            
            // Handle different types of PDF extraction errors
            let errorMessage = 'Failed to extract text from PDF. ';
            
            if (error.name === 'InvalidPDFException') {
                errorMessage += 'The file appears to be corrupted or not a valid PDF.';
            } else if (error.message && error.message.includes('password')) {
                errorMessage += 'The PDF is password protected.';
            } else {
                errorMessage += 'This may be a scanned PDF without OCR text layer. Please submit a PDF with selectable text.';
            }
            
            filenameWarning.textContent = errorMessage;
            setSubmitDisabled(true);
            pdfFileInput.value = '';
            updateFileStatus('PDF text extraction failed.', 'error');
            resultBox.innerHTML = `
                <div class="flex flex-col items-center justify-center py-4 text-center">
                    <svg class="h-12 w-12 text-red-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm font-medium text-red-600">PDF Error</p>
                    <p class="text-xs text-red-500 mt-1">${errorMessage}</p>
                </div>`;
            return;
        }

        // Run plagiarism check only if we successfully extracted text
        if (typeof window.runLivePdfPlagiarismCheck === 'function') {
            window.runLivePdfPlagiarismCheck();
        }
    });

    // allow selecting same file twice
    pdfFileInput.addEventListener('click', () => { 
        // Don't reset if we're currently scanning
        if (!isScanning) {
            pdfFileInput.value = ''; 
        }
    });
} else {
    console.error("pdf.js is not loaded or pdfFileInput missing.");
}

        // ==== Guard submit if similarity too high ====
        document.getElementById('pdf-upload-form')?.addEventListener('submit', (e) => {
            if (lastScore !== null && !isNaN(Number(lastScore)) && Number(lastScore) >= BLOCK_THRESHOLD){
                e.preventDefault();
                resultBox.innerHTML += `<div class="mt-2 text-sm text-red-600">Submission blocked due to high similarity (${lastScore}%).</div>`;
            }
        });

        // ==== Off-canvas handlers (View Matches) ====
        function openOff(){ offcanvas?.classList.remove('hidden'); }
        function closeOff(){ offcanvas?.classList.add('hidden'); }
        dim?.addEventListener('click', closeOff);
        closeBtn?.addEventListener('click', closeOff);

        viewBtn?.addEventListener('click', async () => {
            // If we have recent scan data, use it immediately
            if (currentScanData && (Date.now() - currentScanData.timestamp < 30000)) { // 30 seconds cache
                renderOffcanvasMatches(currentScanData);
                openOff();
                return;
            }

            // Otherwise, run a new scan
            const file = pdfFileInput?.files?.[0] || null;
            const txt  = (pdfTextArea?.value || '').trim();

            if (!file && !txt) {
                alert('Please select a file first.');
                return;
            }

            openOff();
            bodyBox.innerHTML = `
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                        <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
                    </svg>
                    <span>Scanning for detailed matches…</span>
                </div>`;

            try {
                let data, res;
                if (file){
                    const fd = new FormData();
                    fd.append('file', file);
                    fd.append('_token', '{{ csrf_token() }}');
                    res  = await fetch("{{ route('research-papers.check-plagiarism-detailed') }}", { method:'POST', body: fd });
                } else {
                    res  = await fetch("{{ route('research-papers.check-plagiarism-detailed') }}", {
                        method:'POST',
                        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                        body: JSON.stringify({ pdf_text: txt })
                    });
                }
                data = await readJsonSafe(res);
                if (!res.ok) {
                    bodyBox.innerHTML = `<div class="rounded border bg-red-50 p-4 text-red-700">Server error (${res.status}). Please try again.</div>`;
                    return;
                }

                // Store the data for future use
                currentScanData = {
                    score: Number(data.score ?? 0),
                    matches: Array.isArray(data.matches) ? data.matches : [],
                    timestamp: Date.now()
                };

                renderOffcanvasMatches(currentScanData);
                
            } catch (err) {
                console.error(err);
                bodyBox.innerHTML = `<div class="rounded border bg-red-50 p-4 text-red-700">Error generating matches. Please try again.</div>`;
            }
        });

        function renderOffcanvasMatches(scanData) {
            const matches = scanData.matches || [];
            const score = scanData.score || 0;

            if (!matches.length) {
                bodyBox.innerHTML = `
                    <div class="space-y-3">
                        <div class="text-sm text-gray-600">Overall Score: <strong>${isNaN(score)?'—':score+'%'}</strong></div>
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
                    Overall Max Similarity: <strong>${isNaN(score)?'—':score+'%'}</strong> • Showing top ${matches.length} matches
                </div>
                ${cards}
            `;
        }

        // ==== Init ====
        document.addEventListener('DOMContentLoaded', () => {
            setSubmitDisabled(true);
            updateViewMatchesButton();
        });

        // Dismiss alert buttons
        document.querySelectorAll('[data-dismiss="alert"]').forEach(btn => {
            btn.addEventListener('click', () => btn.closest('[id$="-alert"]')?.remove());
        });
    })();
    </script>


<style>
/* Add some custom styles for better error visibility */
.border-error {
    border-color: #dc2626;
}

.text-error {
    color: #dc2626;
}

.bg-error-light {
    background-color: #fef2f2;
}
</style>
</x-userlayout>