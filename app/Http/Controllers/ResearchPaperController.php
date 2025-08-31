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
    public function create()
    {
        return view('pdfconverter.index');
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
