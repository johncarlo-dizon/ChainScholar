<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\ResearchPaper;

class PdfPlagiarismCertificateController extends Controller
{
    public function download(Request $request, ResearchPaper $paper)
    {
        // Authorization - only owner or admin can download
        if (!$request->user()->isAdmin()) {
            if ((int) $paper->user_id !== (int) $request->user()->id) {
                abort(403, 'You are not allowed to download this certificate.');
            }
        }

        // Must have plagiarism score
        $plagiarismScore = $paper->plagiarism_score;
        if (is_null($plagiarismScore)) {
            abort(400, 'Plagiarism analysis not completed for this document.');
        }

        // Determine originality status and colors
        $getSimilarityStatus = function($similarity) {
            if ($similarity < 10) return ['Excellent', '#10b981', 'Highly Original'];
            if ($similarity < 20) return ['Good', '#059669', 'Original'];
            if ($similarity < 30) return ['Acceptable', '#d97706', 'Moderate Similarity'];
            if ($similarity < 40) return ['Caution', '#dc2626', 'High Similarity'];
            return ['Critical', '#991b1b', 'Very High Similarity'];
        };

        [$status, $color, $label] = $getSimilarityStatus($plagiarismScore);
        $originalityPercentage = 100 - $plagiarismScore;

        // Branding
        $brand = 'ResearchPlagiarism';
        $issuer = config('app.name', 'ResearchPlagiarism');
        $siteUrl = url('/');
        $certId = 'PLG-' . date('Ymd', strtotime($paper->created_at)) . '-' . $paper->id;

        // Get submitter information
        $submitterName = $paper->user->name ?? 'Unknown';
        $submitterSafe = e($submitterName);

        // Sanitized fields
        $titleSafe = e((string) $paper->title);
        $authorSafe = e((string) $paper->authors);
        $departmentSafe = e((string) ($paper->department ?? '—'));
        $programSafe = e((string) ($paper->program ?? '—'));

        // Analysis date
        $analysisDate = $paper->updated_at?->format('M d, Y H:i:s \U\T\C') ?? '—';
        $uploadDate = $paper->created_at?->format('M d, Y') ?? '—';

        $html = <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { 
    margin: 12px; 
  }
  body { 
    font-family: DejaVu Sans, Arial, sans-serif; 
    color:#0b1220; 
    background:#ffffff; 
    line-height:1.3;
    margin: 0;
    padding: 0;
    height: 100vh;
    overflow: hidden;
  }
  
  .certificate-container {
    border: 3px solid #0b245b;
    border-radius: 8px;
    height: calc(100vh - 24px);
    position: relative;
    background: white;
    box-shadow: 0 2px 10px rgba(30, 64, 175, 0.1);
    display: flex;
    flex-direction: column;
    margin: 15px;
  }
  
  .header {
    background: #0b245b;
    color: white;
    padding: 10px 20px;
    border-radius: 5px 5px 0 0;
    flex-shrink: 0;
  }
  
  .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .header-title {
    font-size: 18px;
    font-weight: bold;
  }
  
  .header-brand {
    text-align: right;
  }
  
  .brand-name {
    font-size: 16px;
    font-weight: bold;
  }
  
  .certificate-id {
    font-size: 10px;
    opacity: 0.9;
    margin-top: 2px;
  }
  
  .content {
    padding: 15px 20px;
    position: relative;
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
  
  .watermark {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%) rotate(-25deg);
    font-size: 50px;
    font-weight: 900;
    color: #0b245b;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
    white-space: nowrap;
  }
  
  .issuer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
  }
  
  .issuer-info {
    font-size: 11px;
    color: #475569;
  }
  
  .seal {
    border: 2px solid #0b245b;
    border-radius: 20px;
    padding: 5px 10px;
    font-size: 9px;
    font-weight: bold;
    color: #0b245b;
    text-transform: uppercase;
    background: white;
  }
  
  .section-title {
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin: 12px 0 6px 0;
    font-weight: 600;
    flex-shrink: 0;
  }
  
  /* Originality Score */
  .originality-section {
    text-align: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 8px;
    padding: 12px;
    margin: 8px 0;
    border: 1px solid #e5e7eb;
    flex-shrink: 0;
  }
  
  .score-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
    background: conic-gradient(#10b981 0% {$originalityPercentage}%, #e5e7eb {$originalityPercentage}% 100%);
    position: relative;
  }
  
  .score-circle::before {
    content: '';
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: white;
    position: absolute;
  }
  
  .score-text {
    position: relative;
    z-index: 1;
    color: #0f172a;
  }
  
  .score-label {
    font-size: 15px;
    font-weight: bold;
    color: #374151;
  }
  
  /* Similarity Analysis */
  .similarity-card {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 10px;
    background: white;
    margin: 8px 0;
    flex-shrink: 0;
  }
  
  .similarity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
  }
  
  .similarity-type {
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
  }
  
  .similarity-value {
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .percentage {
    font-size: 12px;
    font-weight: bold;
    color: #0b1220;
  }
  
  .status-badge {
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 8px;
    font-weight: bold;
    color: white;
  }
  
  .similarity-bar {
    height: 14px;
    background: #e5e7eb;
    border-radius: 7px;
    margin: 5px 0;
    overflow: hidden;
    position: relative;
  }
  
  .similarity-fill {
    height: 100%;
    border-radius: 7px;
  }
  
  .bar-label {
    position: absolute;
    left: 6px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 8px;
    font-weight: bold;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
  }
  
  .similarity-description {
    font-size: 9px;
    color: #64748b;
    text-align: center;
  }
  
  /* Document Information */
  .document-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin: 6px 0;
    flex-shrink: 0;
  }
  
  .info-card {
    border: 1px solid #e5e7eb;
    border-radius: 5px;
    padding: 8px;
    background: white;
  }
  
  .full-width {
    grid-column: 1 / -1;
  }
  
  .info-label {
    font-size: 9px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 3px;
  }
  
  .info-value {
    font-size: 10px;
    font-weight: 600;
    color: #0b1220;
    line-height: 1.3;
  }
  
  /* Interpretation Guide */
  .interpretation-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
    margin: 6px 0;
    flex-shrink: 0;
  }
  
  .interpretation-item {
    padding: 5px 6px;
    border-radius: 4px;
    font-size: 8px;
    line-height: 1.2;
  }
  
  .interpretation-label {
    font-weight: bold;
    margin-bottom: 1px;
    font-size: 8px;
  }
  
  /* Footer */
  .footer {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid #e5e7eb;
    font-size: 8px;
    color: #475569;
    line-height: 1.3;
    text-align: center;
    flex-shrink: 0;
  }
  
  .main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
</style>
</head>
<body>
  <div class="certificate-container">
    <div class="header">
      <div class="header-content">
        <div class="header-title">Plagiarism Originality Certificate</div>
        <div class="header-brand">
          <div class="brand-name">{$issuer}</div>
          <div class="certificate-id">Certificate ID: {$certId}</div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="watermark">ORIGINALITY REPORT</div>
      
      <div class="issuer-row">
        <div class="issuer-info">Issued by {$issuer}</div>
        <div class="seal">Plagiarism Analysis</div>
      </div>

      <div class="main-content">
        <!-- Overall Originality Score -->
        <div class="section-title">Originality Assessment</div>
        <div class="originality-section">
          <div class="score-circle">
            <div class="score-text">{$originalityPercentage}%</div>
          </div>
          <div class="score-label">Overall Originality Score</div>
        </div>

        <!-- Similarity Analysis -->
        <div class="section-title">Similarity Analysis</div>
        <div class="similarity-card">
          <div class="similarity-header">
            <div class="similarity-type">Overall Similarity</div>
            <div class="similarity-value">
              <div class="percentage">{$plagiarismScore}%</div>
              <span class="status-badge" style="background:{$color}">{$status}</span>
            </div>
          </div>
          <div class="similarity-bar">
            <div class="similarity-fill" style="width:{$plagiarismScore}%; background:{$color};">
              <span class="bar-label">{$plagiarismScore}%</span>
            </div>
          </div>
          <div class="similarity-description">{$label}</div>
        </div>

        <!-- Document Information -->
        <div class="section-title">Document Information</div>
        <div class="document-grid">
          <div class="info-card full-width">
            <div class="info-label">Title</div>
            <div class="info-value">{$titleSafe}</div>
          </div>
          
          <div class="info-card">
            <div class="info-label">Author(s)</div>
            <div class="info-value">{$authorSafe}</div>
          </div>
          
          <div class="info-card">
            <div class="info-label">Submitted By</div>
            <div class="info-value">{$submitterSafe}</div>
          </div>
          
          <div class="info-card">
            <div class="info-label">Department</div>
            <div class="info-value">{$departmentSafe}</div>
          </div>
          
          <div class="info-card">
            <div class="info-label">Program</div>
            <div class="info-value">{$programSafe}</div>
          </div>
          
          <div class="info-card">
            <div class="info-label">Upload Date</div>
            <div class="info-value">{$uploadDate}</div>
          </div>
          
          <div class="info-card">
            <div class="info-label">Analysis Date</div>
            <div class="info-value">{$analysisDate}</div>
          </div>
        </div>

        <!-- Footer -->
        <div class="footer">
          This certificate provides an originality assessment based on automated plagiarism detection. 
          The analysis was conducted on {$analysisDate}.<br>
          <strong>{$issuer}</strong> • {$siteUrl} • Certificate ID: {$certId}
        </div>
      </div>
    </div>
  </div>
</body>
</html>
HTML;

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('defaultPaperSize', 'A4');
        $options->set('dpi', 96);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'plagiarism-certificate-' . $paper->id . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}