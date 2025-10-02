<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\ResearchPaper; // adjust model namespace if different

class CertificateController extends Controller
{
    public function download(Request $request, ResearchPaper $paper)
    {
        // Authorization (owner or admin)
        if (!$request->user()->isAdmin()) {
            if ((int) $paper->user_id !== (int) $request->user()->id) {
                abort(403, 'You are not allowed to download this certificate.');
            }
        }

        // Must be registered/confirmed and must have tx_hash + sha256
        $status = strtoupper((string) ($paper->chain_status ?? ''));
        $isRegistered = in_array($status, ['REGISTERED', 'CONFIRMED'], true);
        if (!$isRegistered || empty($paper->tx_hash) || empty($paper->sha256)) {
            abort(400, 'Certificate is only available once the paper is registered on-chain.');
        }

        // Map chain → branding
        $chainId = (int) ($paper->chain_id ?? 0);
        [$networkName, $explorerBase] = match ($chainId) {
            80002    => ['Polygon Amoy (80002)', 'https://amoy.polygonscan.com/tx/'],
            11155111 => ['Ethereum Sepolia (11155111)', 'https://sepolia.etherscan.io/tx/'],
            default  => ['Unknown Network (' . $chainId . ')', null],
        };

        // Timestamp: prefer confirmed_at if any (UTC)
        $tsUtc    = $paper->confirmed_at ?: $paper->created_at;
        $tsUtcIso = gmdate('M-d-Y H:i:s \U\T\C', strtotime($tsUtc));

        $brand = 'ChainScholar';
        $hash  = (string) $paper->sha256;
        $tx    = (string) $paper->tx_hash;

        $txUrl   = ($explorerBase && $tx) ? $explorerBase . $tx : '';
        $txShort = $tx ? (mb_substr($tx, 0, 12) . '…' . mb_substr($tx, -12)) : '—';

        // Optional QR for transaction link (remote image; Dompdf isRemoteEnabled=true)
        $qrUrl = $txUrl ? ('https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($txUrl)) : '';

        // Sanitized fields
        $title      = e((string) $paper->title);
        $author     = e((string) $paper->authors);
        $statusHtml = e((string) ($paper->chain_status ?? ''));
        $block      = e((string) ($paper->block_number ?? '—'));
        $hashHtml   = e($hash);

        // Presentation bits (keep logic out of heredoc)
        $chipClass      = $isRegistered ? 'reg' : 'unreg';
        $certId         = 'CS-' . date('Ymd', strtotime($tsUtc)) . '-' . $paper->id;
        $issuer         = config('app.name', 'ChainScholar');
        $siteUrl        = url('/');
        $verifyUrl      = $txUrl ?: ($siteUrl . '/verify?sha=' . urlencode($hash));
        $logoUrl        = config('app.logo_url', ''); // set APP_LOGO_URL / config('app.logo_url') if you have one
        $brandSafe      = e($brand);
        $issuerSafe     = e($issuer);
        $certIdSafe     = e($certId);
        $verifyUrlSafe  = e($verifyUrl);

        // Header brand block (logo fallback) — force white text on fallback
        $brandHeaderHtml = $logoUrl
            ? '<img src="' . e($logoUrl) . '" alt="' . $brandSafe . ' Logo" style="height:36px; display:block;">'
            : '<div style="font-weight:700; font-size:16px; color:#ffffff;">' . $brandSafe . '</div>';

        // QR image html (optional)
        $qrImgHtml = $qrUrl
            ? '<img src="' . e($qrUrl) . '" alt="Verify QR" style="width:120px; height:120px; display:block;">'
            : '<div style="width:120px; height:120px; border:1px dashed #cbd5e1; display:flex; align-items:center; justify-content:center; font-size:10px; color:#94a3b8;">No QR</div>';

        // TX link html (with wrapping)
        $txLinkHtml = $txUrl
            ? '<a class="break" href="' . e($txUrl) . '" style="color:#1e2a78; text-decoration:none;">' . e($txUrl) . '</a>'
            : '—';

        // HTML
        $html = <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  /* Page + base (tighter to keep to a single page) */
  @page { margin: 20px; }
  body { font-family: DejaVu Sans, Arial, sans-serif; color:#0b1220; background:#ffffff; line-height:1.25; }


  /* Card shell (avoid page breaks) */
  .card { border:2px solid #0b245b; border-radius:8px; page-break-inside: avoid; }
.p { padding:4px 16px; margin-top:-22px; position:relative; }



  .p > * { position:relative; z-index:1; }

  /* Header band (slightly tighter) */
.hdr { background:#0b245b; color:#fff; padding:8px 16px; border-radius:6px 6px 0 0; margin-bottom:0; }


.hdr-table { width:100%; border-collapse:collapse; margin:0; }
table { border-collapse:collapse; border-spacing:0; }
.hdr-table td { padding:0; margin:0; }

  .hdr-left { font-size:18px; font-weight:700; color:#ffffff; }
  .hdr-right { text-align:right; }
  .subtitle { font-size:11px; opacity:.9; }
  .hdr .brand .logo { color:#ffffff !important; }
.hdr + .p { margin-top:-54px; }

  /* Watermark (centered, smaller) */
  .wm {
  position:absolute;
  left:50%;
  top:55%;
  transform:translate(-50%, -50%) rotate(-20deg);
  width:100%;
  text-align:center;
  font-size:64px;
  font-weight:800;
  letter-spacing:4px;
  color:#0b245b;
  opacity:.05;
  pointer-events:none;
  z-index:0;
}


  /* Title row */
.title-row { margin:0 0 6px; display:flex; align-items:center; justify-content:space-between; }



  .seal { border:2px solid #0b245b; border-radius:999px; padding:5px 9px; font-size:9px; font-weight:700; color:#0b245b; text-transform:uppercase; }

  /* Chips */
  .chip { display:inline-block; padding:3px 8px; font-size:10px; font-weight:700; border-radius:999px; border:1px solid #e2e8f0; }
  .chip.reg { background:#fff7e6; color:#92400e; border-color:#f7c76d; }
  .chip.unreg { background:#f1f5f9; color:#0f172a; border-color:#e2e8f0; }

  /* Sections + boxes (tighter spacing) */
  .section-title { font-size:10px; color:#64748b; text-transform:uppercase; letter-spacing:.08em; margin:8px 0 4px; }
  .grid { width:100%; border-collapse:separate; border-spacing:10px 8px; }
  .box { border:1px solid #e5e7eb; border-radius:6px; padding:8px 10px; }
  .label { font-size:9px; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px; }
  .value { font-size:12px; font-weight:600; color:#0b1220; }
  .mono  { font-family: DejaVu Sans Mono, monospace; font-size:10px; word-break:break-all; }

  /* Comment */
  .comment { border:1px dashed #cbd5e1; border-radius:6px; padding:8px 10px; background:#fafafa; font-size:11px; }

  /* TX / Verify row */
  .row { width:100%; border-collapse:collapse; }
  .col-left { width:120px; vertical-align:top; }
  .muted { color:#475569; font-size:11px; }

  /* Signatories (smaller spacing) */
  .sig-grid { width:100%; border-collapse:separate; border-spacing:16px 0; margin-top:12px; }
  .sig-box { text-align:center; }
  .sig-line { border-top:1px solid #a3a3a3; margin-top:20px; padding-top:3px; font-size:10px; color:#334155; }

  /* Footer (tighter) */
  .foot { margin-top:6px; font-size:10px; color:#475569; }

  /* Link wrapping */
  a { color:#1e2a78; text-decoration:none; }
  .break { word-break: break-all; overflow-wrap: anywhere; }
</style>
</head>
<body>
  <div class="card">
    <div class="hdr">
      <table class="hdr-table">
        <tr>
          <td class="hdr-left">Timestamp Certificate</td>
          <td class="hdr-right">
            {$brandHeaderHtml}
            <div class="subtitle">Certificate ID: {$certIdSafe}</div>
          </td>
        </tr>
      </table>
    </div>

    <div class="p">
      <div class="wm">{$brandSafe}</div>

     <div class="title-row" style="margin-top:4px; margin-bottom:8px;">
        <div class="muted">Issued by {$issuerSafe}</div>
        <div class="seal">PROOF OF EXISTENCE</div>
      </div>

      <!-- Summary badges -->
      <div class="section-title">Summary</div>
      <table class="grid">
        <tr>
          <td class="box">
            <div class="label">Status</div>
            <div class="value"><span class="chip {$chipClass}">{$statusHtml}</span></div>
          </td>
          <td class="box">
            <div class="label">Timestamp (UTC)</div>
            <div class="value">{$tsUtcIso}</div>
          </td>
        </tr>
      </table>

      <!-- Document metadata -->
      <div class="section-title">Document</div>
      <table class="grid">
        <tr>
          <td class="box" colspan="2">
            <div class="label">Title</div>
            <div class="value">{$title}</div>
          </td>
        </tr>
        <tr>
          <td class="box">
            <div class="label">Author(s)</div>
            <div class="value">{$author}</div>
          </td>
          <td class="box">
            <div class="label">Network</div>
            <div class="value">{$networkName}</div>
          </td>
        </tr>
        <tr>
          <td class="box" colspan="2">
            <div class="label">Hash (SHA-256)</div>
            <div class="mono">{$hashHtml}</div>
          </td>
        </tr>
        <tr>
          <td class="box">
            <div class="label">Block</div>
            <div class="value">{$block}</div>
          </td>
          <td class="box">
            <div class="label">Certificate ID</div>
            <div class="value">{$certIdSafe}</div>
          </td>
        </tr>
      </table>

      <!-- Comment -->
      <div class="section-title">Comment</div>
      <div class="comment">
        Proof of authorship for <b>{$title}</b> by <b>{$author}</b>.
      </div>

      <!-- Verify / TX -->
      <div class="section-title">Verification</div>
      <table class="row">
        <tr>
          <td class="col-left">
            {$qrImgHtml}
          </td>
          <td>
            <div class="label">Verification URL</div>
            <div class="value break" style="margin-bottom:6px;">
              <a class="break" href="{$verifyUrlSafe}">{$verifyUrlSafe}</a>
            </div>
            <div class="label">Transaction (Explorer)</div>
            <div class="value break" style="margin-bottom:6px;">{$txLinkHtml}</div>
            <div class="label">TX Hash (short)</div>
            <div class="mono">{$txShort}</div>
          </td>
        </tr>
      </table>

   

      <!-- Footer -->
      <div class="foot">
        This certificate is valid only with the original file and a matching on-chain record. Keep this PDF with your research file for verification.<br>
        {$issuerSafe} • {$siteUrl}
      </div>
      <br>
      <br>
    </div>
  </div>
</body>
</html>
HTML;

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'chainscholar-certificate-' . $paper->id . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
