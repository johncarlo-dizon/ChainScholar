<?php
// app/Http/Controllers/PdfPlagiarismController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PdfPlagiarismService;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Log;
use Throwable;

class PdfPlagiarismController extends Controller
{
    public function __construct(private PdfPlagiarismService $plag) {}

    /** Quick score from raw text or uploaded file (no save) */
    public function checkPdfPlagiarismLive(Request $request)
    {
        try {
            // Prefer client-extracted text
            $text = trim((string) $request->input('pdf_text', ''));

            if ($text === '' && $request->hasFile('file')) {
                $file = $request->file('file');
                if (!$file->isValid()) {
                    return response()->json([
                        'error' => 'Uploaded file is not valid.'
                    ], 400);
                }

                try {
                    $parser = new PdfParser();
                    $file   = $request->file('file');
                    $pdf    = $parser->parseFile($file->getRealPath());
                    $text   = (string) $pdf->getText();

                } catch (Throwable $e) {
                    Log::channel('pdfplag')->warning('PDF parse failed', [
                        'msg' => $e->getMessage(),
                        'code'=> $e->getCode(),
                    ]);
                    // Don’t explode the UI — return score 0 with a warning
                    return response()->json([
                        'score'   => 0,
                        'warning' => 'Unable to parse PDF text. Try another PDF or ensure it contains selectable text.'
                    ], 200);
                }
            }

            if ($text === '') {
                // Graceful fallback — don’t raise a 4xx/5xx that the UI can’t parse
                return response()->json([
                    'score'   => 0,
                    'warning' => 'No text detected in this file.'
                ], 200);
            }

            $score = $this->plag->quickScoreFromText($text);
            return response()->json(['score' => $score], 200);

        } catch (Throwable $e) {
            Log::channel('pdfplag')->error('Live plagiarism check crashed', [
                'msg' => $e->getMessage(), 'trace' => $e->getTraceAsString(),
            ]);
            // Always JSON
            return response()->json([
                'error' => 'Internal checker error.'
            ], 500);
        }
    }

    /** Detailed matches from text/file */
    public function checkPdfPlagiarismDetailed(Request $request)
    {
        try {
            $min  = (int) $request->input('min_percent', 0);
            $text = trim((string) $request->input('pdf_text', ''));

            if ($text === '' && $request->hasFile('file')) {
                $file = $request->file('file');
                if (!$file->isValid()) {
                    return response()->json(['error' => 'Uploaded file is not valid.'], 400);
                }
                try {
                    $parser = new PdfParser();
                    $pdf    = $parser->parseFile($file->getRealPath());
                    $text   = (string) $pdf->getText();
                } catch (Throwable $e) {
                    Log::channel('pdfplag')->warning('PDF parse (detailed) failed', ['msg'=>$e->getMessage()]);
                    return response()->json([
                        'score'   => 0,
                        'matches' => [],
                        'aggregate' => [],
                        'meta'    => ['note' => 'Unable to parse PDF text.'],
                    ], 200);
                }
            }

            if ($text === '') {
                return response()->json([
                    'score'   => 0,
                    'matches' => [],
                    'aggregate' => [],
                    'meta'    => ['note' => 'No text detected.'],
                ], 200);
            }

            $data = $this->plag->detailedMatchesFromText($text, $min);
            return response()->json($data, 200);

        } catch (Throwable $e) {
            Log::channel('pdfplag')->error('Detailed plagiarism check crashed', [
                'msg' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Internal checker error.'
            ], 500);
        }
    }
}
