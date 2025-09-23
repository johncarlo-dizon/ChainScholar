<?php

// app/Http/Controllers/ResearchPaperController.php
// app/Http/Controllers/ResearchPaperController.php

namespace App\Http\Controllers;

use App\Models\ResearchPaper;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Storage;
use App\Services\PdfPlagiarismService;
use Illuminate\Support\Facades\Cache;
 

class ResearchPaperController extends Controller
{



    // app/Http/Controllers/ResearchPaperController.php

    public function viewStudentPdf(Request $request)
    {
        $user = auth()->user();
        
        $papers = ResearchPaper::where('user_id', $user->id)
            ->when($request->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('authors', 'like', "%{$search}%")
                    ->orWhere('abstract', 'like', "%{$search}%");
                });
            })
            ->when($request->department, function($query, $department) {
                return $query->where('department', $department);
            })
            ->when($request->program, function($query, $program) {
                return $query->where('program', $program);
            })
            ->when($request->year, function($query, $year) {
                return $query->where('year', $year);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $departments = ResearchPaper::where('user_id', $user->id)
            ->distinct('department')
            ->pluck('department');
            
        $programs = ResearchPaper::where('user_id', $user->id)
            ->distinct('program')
            ->pluck('program');
            
        $years = ResearchPaper::where('user_id', $user->id)
            ->distinct('year')
            ->pluck('year')
            ->sort();

        return view('research-papers.student-index', compact('papers', 'departments', 'programs', 'years'));
    }

    public function viewAdminPdf(Request $request)
    {
        $papers = ResearchPaper::with('user')
            ->when($request->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('authors', 'like', "%{$search}%")
                    ->orWhere('abstract', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->department, function($query, $department) {
                return $query->where('department', $department);
            })
            ->when($request->program, function($query, $program) {
                return $query->where('program', $program);
            })
            ->when($request->year, function($query, $year) {
                return $query->where('year', $year);
            })
            ->when($request->user, function($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $departments = ResearchPaper::distinct('department')
            ->pluck('department');
            
        $programs = ResearchPaper::distinct('program')
            ->pluck('program');
            
        $years = ResearchPaper::distinct('year')
            ->pluck('year')
            ->sort();
            
        $users = \App\Models\User::whereHas('researchPapers')
            ->get(['id', 'name', 'email']);

        return view('research-papers.admin-index', compact('papers', 'departments', 'programs', 'years', 'users'));
    }



    // app/Http/Controllers/ResearchPaperController.php

    public function destroyAdminPdf(ResearchPaper $researchPaper)
    {
        // Check if the user is authorized to delete (admin only)
        if (auth()->user()->role !== 'ADMIN') {
            abort(403, 'Unauthorized action.');
        }

        // Delete the file from storage
        if (Storage::disk('public')->exists($researchPaper->file_path)) {
            Storage::disk('public')->delete($researchPaper->file_path);
        }

        // Delete the record from database
        $researchPaper->delete();

        return redirect()->route('research-papers.admin-index')
            ->with('status', 'Research paper deleted successfully.');
    }



    public function destroyUserPdf(ResearchPaper $researchPaper)
    {
     
        // Delete the file from storage
        if (Storage::disk('public')->exists($researchPaper->file_path)) {
            Storage::disk('public')->delete($researchPaper->file_path);
        }

        // Delete the record from database
        $researchPaper->delete();

        return redirect()->route('research-papers.student-index')
            ->with('status', 'Research paper deleted successfully.');
    }




    //------------------------------------------------------
    public function create()
    {
        return view('research-papers.index');
    }



 private function normalizeForHash(string $text): string
    {
        // Collapse whitespace
        $t = preg_replace('/\s+/u', ' ', trim($text));
        // Remove soft hyphen and join hyphenated line-breaks
        $t = str_replace("\u{00AD}", '', $t);
        $t = preg_replace("/-\s+/", '', $t);
        // Unify curly apostrophes → straight
        $t = preg_replace('/[’]/u', "'", $t);
        // Lowercase
        $t = mb_strtolower($t, 'UTF-8');
        return $t;
    }

    /**
     * Store a submitted research paper:
     * - Server-extract PDF text
     * - Gate with plagiarism score (pre-save)
     * - Save file + DB record (including text_hash)
     * - Bust caches so it participates in checks immediately
     */
    public function store(Request $request, PdfPlagiarismService $pdfPlag)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'year'         => 'required|integer|min:1900|max:2099',
            'authors'      => 'required|string|max:255',
            'department'   => 'required|string',
            'program'      => 'required|string',
            'abstract'     => 'required|string',
            'fileToUpload' => 'required|file|mimes:pdf|max:10240', // 10 MB
        ]);

        $user = $request->user();

        // Prevent duplicate filename for THIS user (UI also checks, but enforce server-side)
        $originalFilename = $request->file('fileToUpload')->getClientOriginalName();
        $hasSameName = $user->researchPapers()
            ->where('filename', $originalFilename)
            ->exists();

        if ($hasSameName) {
            return back()
                ->with('error', 'You already have a file with this name. Please rename your file.')
                ->withInput();
        }

        // ---- 1) Parse PDF text (server-side) BEFORE any save
        $extractedText = '';
        try {
            $pdfParser = new PdfParser();
            $pdf       = $pdfParser->parseFile($request->file('fileToUpload')->path());
            $extractedText = (string) $pdf->getText();
        } catch (\Throwable $e) {
            Log::error('PDF text extraction failed: ' . $e->getMessage());
            // We still proceed, but plagiarism gate will likely return 0 if text is empty.
        }

        // ---- 2) Plagiarism gate BEFORE saving (align with your UI threshold)
        $BLOCK_THRESHOLD = 45; // same as in your Blade script
        $score = 0.0;
        try {
            $score = $pdfPlag->quickScoreFromText($extractedText ?? '');
        } catch (\Throwable $e) {
            Log::warning('Plagiarism quickScore error: ' . $e->getMessage());
        }

        if ($score >= $BLOCK_THRESHOLD) {
            // Optional context for the user (top aggregated match)
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
                Log::info('Detailed matches failed (non-fatal): ' . $e->getMessage());
            }

            return back()
                ->with('error', $msg . ' Please revise your paper and try again.')
                ->withInput();
        }

        // ---- 3) Store the file (now that it passed the gate)
        // Keep storage unique even if user filenames collide later.
        $path = $request->file('fileToUpload')->store('research_papers', 'public');
        if (!$path) {
            return back()
                ->with('error', 'Failed to store the uploaded file. Please try again.')
                ->withInput();
        }

        // ---- 4) Compute normalized text hash (for exact-duplicate detection)
        $textHash = null;
        if (!empty($extractedText)) {
            $normalized = $this->normalizeForHash($extractedText);
            if ($normalized !== '') {
                $textHash = hash('sha256', $normalized);
            }
        }

        // ---- 5) Create DB record (and include extracted_text + text_hash)
        $paper = $user->researchPapers()->create([
            'title'          => $request->string('title'),
            'year'           => (int) $request->input('year'),
            'authors'        => $request->string('authors'),
            'department'     => $request->string('department'),
            'program'        => $request->string('program'),
            'abstract'       => $request->string('abstract'),
            'filename'       => $originalFilename,
            'file_path'      => $path,
            'extracted_text' => $extractedText ?: null,
            'text_hash'      => $textHash,
        ]);

        // ---- 6) Bust caches so this paper is immediately included in the corpus
        Cache::forget('plag:candidates:pdf:v1');
        Cache::forget('plag:stats:pdf:v1');

        return redirect()
            ->route('research-papers.create')
            ->with('status', 'Research paper uploaded successfully!');
    }

    public function checkFilename(Request $request)
    {
        $filename = $request->query('filename');
        $exists = auth()->user()->researchPapers()
                              ->where('filename', $filename)
                              ->exists();
        
        return response()->json(['exists' => $exists]);
    }
}
