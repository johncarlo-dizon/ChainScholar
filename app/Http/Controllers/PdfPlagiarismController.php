<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PdfPlagiarismService;
use Smalot\PdfParser\Parser as PdfParser;

class PdfPlagiarismController extends Controller
{
    public function __construct(private PdfPlagiarismService $plag) {}

    /** Quick score from raw text or uploaded file (no save) */
    public function checkPdfPlagiarismLive(Request $request)
    {
        // Accept either 'pdf_text' (client-extracted) or 'file' (server-parse)
        $text = trim((string)$request->input('pdf_text', ''));

        if ($text === '' && $request->hasFile('file')) {
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($request->file('file')->path());
                $text = (string)$pdf->getText();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Unable to parse PDF text.'], 422);
            }
        }

        $score = $this->plag->quickScoreFromText($text);
        return response()->json(['score' => $score]);
    }

    /** Detailed matches from text/file */
    public function checkPdfPlagiarismDetailed(Request $request)
    {
        $min = (int)$request->input('min_percent', 0);
        $text = trim((string)$request->input('pdf_text', ''));

        if ($text === '' && $request->hasFile('file')) {
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($request->file('file')->path());
                $text = (string)$pdf->getText();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Unable to parse PDF text.'], 422);
            }
        }

        $data = $this->plag->detailedMatchesFromText($text, $min);
        return response()->json($data);
    }
}
