<?php

namespace App\Http\Controllers;

use App\Models\ResearchPaper;
use App\Services\PdfPlagiarismService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Str;

class ResearchPaperController extends Controller
{
    /** Per-feature log channels */
    private function logUpload() { return Log::channel('pdfupload'); }
    private function logParser() { return Log::channel('pdfparser'); }
    private function logPlag()   { return Log::channel('pdfplag');   }

    /** Upload page */
    public function create()
    {
        return view('research-papers.index');
    }

    /** Student list */
    public function viewStudentPdf(Request $request)
    {
         $user = $request->user();

    $papers = ResearchPaper::with(['pendingRequest','lastRequest']) // <-- add this
        ->where('user_id', $user->id)
        ->when($request->filled('search'), function ($q) use ($request) {
            $s = $request->string('search');
            $q->where(function ($qq) use ($s) {
                $qq->where('title', 'like', "%{$s}%")
                   ->orWhere('authors', 'like', "%{$s}%")
                   ->orWhere('abstract', 'like', "%{$s}%");
            });
        })
        ->when($request->filled('department'), fn ($q) => $q->where('department', $request->string('department')))
        ->when($request->filled('program'),    fn ($q) => $q->where('program',    $request->string('program')))
        ->when($request->filled('year'),       fn ($q) => $q->where('year',       (int) $request->input('year')))
        ->orderByDesc('created_at')
        ->paginate(10)
        ->withQueryString();

        // distinct(column) → use select()->distinct()
        $departments = ResearchPaper::select('department')
            ->where('user_id', $user->id)
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $programs = ResearchPaper::select('program')
            ->where('user_id', $user->id)
            ->whereNotNull('program')
            ->distinct()
            ->orderBy('program')
            ->pluck('program');

        $years = ResearchPaper::select('year')
            ->where('user_id', $user->id)
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('research-papers.student-index', compact('papers', 'departments', 'programs', 'years'));
    }

    /** Admin list */
    public function viewAdminPdf(Request $request)
    {
        $papers = ResearchPaper::with('user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search');
                $q->where(function ($qq) use ($s) {
                    $qq->where('title', 'like', "%{$s}%")
                       ->orWhere('authors', 'like', "%{$s}%")
                       ->orWhere('abstract', 'like', "%{$s}%")
                       ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$s}%")
                                                           ->orWhere('email','like', "%{$s}%"));
                });
            })
            ->when($request->filled('department'), fn ($q) => $q->where('department', $request->string('department')))
            ->when($request->filled('program'),    fn ($q) => $q->where('program',    $request->string('program')))
            ->when($request->filled('year'),       fn ($q) => $q->where('year',       (int) $request->input('year')))
            // prefer explicit param name to avoid confusion with Request::user()
            ->when($request->filled('user'),       fn ($q) => $q->where('user_id',    (int) $request->input('user')))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $departments = ResearchPaper::select('department')->whereNotNull('department')->distinct()->orderBy('department')->pluck('department');
        $programs    = ResearchPaper::select('program')->whereNotNull('program')->distinct()->orderBy('program')->pluck('program');
        $years       = ResearchPaper::select('year')->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year');
        $users       = \App\Models\User::whereHas('researchPapers')->get(['id','name','email']);

        return view('research-papers.admin-index', compact('papers','departments','programs','years','users'));
    }

    /** Store upload */
    public function store(Request $request, PdfPlagiarismService $pdfPlag)
    {
        // log PHP / transport layer BEFORE validation
        $this->logUpload()->info('Paper upload attempt (pre-validate)', array_merge(
            ['user_id' => optional($request->user())->id, 'ip' => $request->ip(), 'ua' => substr((string)$request->userAgent(), 0, 200)],
            $this->rawUploadSnapshot($request)
        ));

        try {
            $request->validate([
                'title'        => 'required|string|max:255',
                'year'         => 'required|integer|min:1900|max:2099',
                'authors'      => 'required|string|max:255',
                'department'   => 'required|string',
                'program'      => 'required|string',
                'abstract'     => 'required|string',
                'fileToUpload' => 'required|file|mimes:pdf|max:10240', // 10 MB (Laravel-side)
            ], [
                'fileToUpload.uploaded' => 'The file failed to upload. Please see details below.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errs = $e->validator->errors()->toArray();
            if (isset($errs['fileToUpload'])) {
                $this->logUpload()->warning('Validation failed for file upload', array_merge(
                    ['messages' => $errs['fileToUpload']],
                    $this->rawUploadSnapshot($request)
                ));
            }
            throw $e;
        }

        // if PHP layer failed (e.g. ini caps), surface here too
        if ($err = $this->uploadErrorMessage($request->file('fileToUpload'))) {
            $this->logUpload()->warning('Upload failed at PHP layer', array_merge(
                ['user_id' => optional($request->user())->id, 'error_message' => $err],
                $this->rawUploadSnapshot($request)
            ));
            return back()->withErrors(['fileToUpload' => $err])->withInput();
        }

        $user = $request->user();

        // enforce per-user filename uniqueness (server-side)
        $originalFilename = $request->file('fileToUpload')->getClientOriginalName();
        if ($user->researchPapers()->where('filename', $originalFilename)->exists()) {
            return back()->with('error', 'You already have a file with this name. Please rename your file.')->withInput();
        }

        // 1) extract text (server)
        $extractedText = '';
        try {
            $pdf = (new PdfParser())->parseFile($request->file('fileToUpload')->path());
            $extractedText = (string) $pdf->getText();
        } catch (\Throwable $e) {
            $this->logParser()->error('PDF text extraction failed', ['msg' => $e->getMessage()]);
        }

        // 2) plagiarism gate
        $BLOCK_THRESHOLD = 45;
        $score = 0.0;
        try {
            $score = $pdfPlag->quickScoreFromText($extractedText ?? '');
        } catch (\Throwable $e) {
            $this->logPlag()->warning('Plagiarism quickScore error', ['msg' => $e->getMessage()]);
        }

        if ($score >= $BLOCK_THRESHOLD) {
            $msg = "High similarity detected ({$score}%).";
            try {
                $detail = $pdfPlag->detailedMatchesFromText($extractedText ?? '', 0);
                $top    = $detail['aggregate'][0] ?? null;
                if ($top) {
                    $srcTitle = e($top['source_title'] ?? 'Unknown');
                    $srcType  = e($top['source_type'] ?? 'Corpus');
                    $srcPct   = $top['max_percent'] ?? '—';
                    $msg     .= " Top source: \"{$srcTitle}\" ({$srcType}, {$srcPct}%).";
                }
            } catch (\Throwable $e) {
                $this->logPlag()->info('Detailed matches failed (non-fatal)', ['msg' => $e->getMessage()]);
            }
            return back()->with('error', $msg . ' Please revise your paper and try again.')->withInput();
        }

        // 3) store file
        $disk   = 'public';
        $path   = null;
        try {
            $uploaded  = $request->file('fileToUpload');
            $directory = 'research_papers';
            $unique    = (string) Str::uuid() . '.pdf';

            $path = Storage::disk($disk)->putFileAs($directory, $uploaded, $unique);

            if (!$path || !Storage::disk($disk)->exists($path)) {
                $this->logUpload()->error('Storage write failed', compact('disk','directory','unique'));
                return back()->withErrors(['fileToUpload' => 'Failed to store the uploaded file (disk write).'])->withInput();
            }
        } catch (\Throwable $e) {
            $this->logUpload()->error('Exception during file store', ['msg' => $e->getMessage()]);
            return back()->withErrors(['fileToUpload' => 'Server error while saving your file. Please try again.'])->withInput();
        }

        // 4) file sha256
        $sha256 = null;
        try {
            $stream = Storage::disk($disk)->readStream($path);
            if ($stream === false) throw new \RuntimeException('Failed to open file stream for hashing');
            $hctx = hash_init('sha256');
            while (!feof($stream)) { hash_update($hctx, fread($stream, 8192)); }
            fclose($stream);
            $sha256 = hash_final($hctx);
        } catch (\Throwable $e) {
            $this->logUpload()->warning('SHA256 compute failed', ['msg' => $e->getMessage()]);
        }

        // 5) normalized-text hash for exact dupes
        $textHash = null;
        if ($extractedText !== '') {
            $normalized = $this->normalizeForHash($extractedText);
            if ($normalized !== '') $textHash = hash('sha256', $normalized);
        }

        // 6) DB row
        $user->researchPapers()->create([
            'title'          => (string) $request->input('title'),
            'year'           => (int)    $request->input('year'),
            'authors'        => (string) $request->input('authors'),
            'department'     => (string) $request->input('department'),
            'program'        => (string) $request->input('program'),
            'abstract'       => (string) $request->input('abstract'),
            'plagiarism_score' => (int) round($score), // persist server-computed percent

            'filename'       => (string) $originalFilename,
            'file_path'      => (string) $path,
            'file_disk'      => (string) $disk,
            'sha256'         => $sha256,
            'chain_status'   => 'UPLOADED',
            'extracted_text' => $extractedText ?: null,
            'text_hash'      => $textHash,
        ]);

        // 7) cache bust
        Cache::forget('plag:candidates:pdf:v1');
        Cache::forget('plag:stats:pdf:v1');

        $this->logUpload()->info('Upload completed', ['user_id' => $user->id, 'path' => $path, 'sha256' => $sha256]);

        return redirect()->route('research-papers.create')->with('status', 'Research paper uploaded successfully!');
    }

    /** Student delete (owns the paper) */
    public function destroyUserPdf(ResearchPaper $researchPaper)
    {
        abort_if($researchPaper->user_id !== auth()->id(), 403);

        if (Storage::disk('public')->exists($researchPaper->file_path)) {
            Storage::disk('public')->delete($researchPaper->file_path);
        }
        $researchPaper->delete();

        return redirect()->route('research-papers.student-index')->with('status', 'Research paper deleted successfully.');
    }

    /** Admin delete */
    public function destroyAdminPdf(ResearchPaper $researchPaper)
    {
        abort_if(optional(auth()->user())->role !== 'ADMIN', 403);

        if (Storage::disk('public')->exists($researchPaper->file_path)) {
            Storage::disk('public')->delete($researchPaper->file_path);
        }
        $researchPaper->delete();

        return redirect()->route('research-papers.admin-index')->with('status', 'Research paper deleted successfully.');
    }

    /** AJAX: per-user filename exists */
    public function checkFilename(Request $request)
    {
        $exists = $request->user()
            ->researchPapers()
            ->where('filename', $request->query('filename'))
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    /* ----------------- helpers ----------------- */

    private function normalizeForHash(string $text): string
    {
        $t = preg_replace('/\s+/u', ' ', trim($text));
        $t = str_replace("\u{00AD}", '', $t);
        $t = preg_replace("/-\s+/", '', $t);
        $t = preg_replace('/[’]/u', "'", $t);
        return mb_strtolower($t, 'UTF-8');
    }

    private function uploadErrorMessage(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) return 'No file was received by the server.';
        $err = $file->getError();
        if ($err === UPLOAD_ERR_OK) return null;

        return match ($err) {
            UPLOAD_ERR_INI_SIZE   => 'The file exceeds the server limit (upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the form limit (MAX_FILE_SIZE).',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded. Please choose a PDF.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server is missing a temporary folder. Contact admin.',
            UPLOAD_ERR_CANT_WRITE => 'Server failed to write the file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
            default               => 'The file failed to upload due to an unknown error (code '.$err.').',
        };
    }

    private function rawUploadSnapshot(Request $request): array
    {
        $f = $_FILES['fileToUpload'] ?? null;
        return [
            'php_files_present'   => array_key_exists('fileToUpload', $_FILES),
            'php_error_code'      => $f['error'] ?? null,
            'php_error_meaning'   => isset($f['error']) ? match ((int)$f['error']) {
                0 => 'UPLOAD_ERR_OK', 1 => 'UPLOAD_ERR_INI_SIZE', 2 => 'UPLOAD_ERR_FORM_SIZE',
                3 => 'UPLOAD_ERR_PARTIAL', 4 => 'UPLOAD_ERR_NO_FILE', 6 => 'UPLOAD_ERR_NO_TMP_DIR',
                7 => 'UPLOAD_ERR_CANT_WRITE', 8 => 'UPLOAD_ERR_EXTENSION', default => 'UNKNOWN',
            } : null,
            'php_name'            => $f['name'] ?? null,
            'php_type'            => $f['type'] ?? null,
            'php_size'            => $f['size'] ?? null,
            'content_length'      => (int)($request->header('Content-Length', 0)),
            'post_max_size'       => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ];
    }
}
