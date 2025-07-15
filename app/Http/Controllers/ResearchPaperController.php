<?php

// app/Http/Controllers/ResearchPaperController.php
namespace App\Http\Controllers;

use App\Models\ResearchPaper;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Storage;

class ResearchPaperController extends Controller
{
    public function create()
    {
        return view('pdfconverter.index');
    }

    public function store(Request $request)
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

        // Check if filename exists for this user
        $originalFilename = $request->file('fileToUpload')->getClientOriginalName();
        if (auth()->user()->researchPapers()
                          ->where('filename', $originalFilename)
                          ->exists()) {
            return back()->with('error', 'You already have a file with this name. Please rename your file.');
        }

        // Store the file
        $filePath = $request->file('fileToUpload')->store('research_papers', 'public');

        // Extract text from PDF
        $extractedText = '';
        try {
            $pdfParser = new PdfParser();
            $pdf = $pdfParser->parseFile($request->file('fileToUpload')->path());
            $extractedText = $pdf->getText();
        } catch (\Exception $e) {
            \Log::error('PDF text extraction failed: ' . $e->getMessage());
        }

        // Create record
        auth()->user()->researchPapers()->create([
            'title' => $request->title,
            'year' => $request->year,
            'authors' => $request->authors,
            'department' => $request->department,
            'program' => $request->program,
            'abstract' => $request->abstract,
            'filename' => $originalFilename,
            'extracted_text' => $extractedText,
            'file_path' => $filePath,
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