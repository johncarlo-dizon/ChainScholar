<?php

// app/Http/Controllers/ResearchPaperController.php
// app/Http/Controllers/ResearchPaperController.php

namespace App\Http\Controllers;

use App\Models\ResearchPaper;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Storage;
use App\Services\PdfPlagiarismService;

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
            ->with('success', 'Research paper deleted successfully.');
    }




    //------------------------------------------------------
    public function create()
    {
        return view('research-papers.index');
    }

    public function store(Request $request, PdfPlagiarismService $pdfPlag)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:2099',
            'authors' => 'required|string|max:255',
            'department' => 'required|string',
            'program' => 'required|string',
            'abstract' => 'required|string',
            'fileToUpload' => 'required|file|mimes:pdf|max:10240',
        ]);

        // Prevent duplicate filename for this user
        $originalFilename = $request->file('fileToUpload')->getClientOriginalName();
        if (auth()->user()->researchPapers()
                          ->where('filename', $originalFilename)
                          ->exists()) {
            return back()->with('error', 'You already have a file with this name. Please rename your file.')
                         ->withInput();
        }

        // ---- NEW: Parse PDF text first (no save yet)
        $extractedText = '';
        try {
            $pdfParser = new PdfParser();
            $pdf = $pdfParser->parseFile($request->file('fileToUpload')->path());
            $extractedText = (string)$pdf->getText();
        } catch (\Exception $e) {
            \Log::error('PDF text extraction failed: ' . $e->getMessage());
        }

        // ---- NEW: Run plagiarism check BEFORE saving the file/record
        $BLOCK_THRESHOLD = 45; // align with your CKEditor flow
        $score = $pdfPlag->quickScoreFromText($extractedText);

        if ($score >= $BLOCK_THRESHOLD) {
            // Optional: fetch a couple of top matches for context
            $detail = $pdfPlag->detailedMatchesFromText($extractedText, 0);
            $top = $detail['aggregate'][0] ?? null;
            $msg = 'High similarity detected ('.$score.'%).';
            if ($top) {
                $msg .= ' Top source: "'.e($top['source_title']).'" ('.$top['source_type'].', '.$top['max_percent'].'%).';
            }

            return back()->with('error', $msg . ' Please revise your paper and try again.')
                         ->withInput();
        }

        // ---- Only now: store file
        $filePath = $request->file('fileToUpload')->store('research_papers', 'public');

        // Create record (save the parsed text for future corpus)
        auth()->user()->researchPapers()->create([
            'title'          => $request->title,
            'year'           => $request->year,
            'authors'        => $request->authors,
            'department'     => $request->department,
            'program'        => $request->program,
            'abstract'       => $request->abstract,
            'filename'       => $originalFilename,
            'extracted_text' => $extractedText,
            'file_path'      => $filePath,
        ]);

        return redirect()->route('research-papers.create')
            ->with('success', 'Research paper uploaded successfully!');
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
