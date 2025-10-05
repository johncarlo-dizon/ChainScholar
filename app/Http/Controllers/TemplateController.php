<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\Document as ModelsDocument;
use App\Models\Template;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
// tiny helper: allow .env override without adding a full config file
// usage: config('app.template_preview_enabled', false)
if (!function_exists('config')) {
    // noop – Laravel always has config()
}

class TemplateController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        if (auth()->check()) {
            $userId = auth()->id();

            // Get templates created by admins OR current user
            $templates = \App\Models\Template::whereHas('user', function ($query) use ($userId) {
                    $query->where('role', 'ADMIN')
                        ->orWhere('id', $userId);
                })
                ->latest()
                ->get();
        } else {
            // If not logged in, show only templates created by admins
            $templates = \App\Models\Template::whereHas('user', function ($query) {
                    $query->where('role', 'ADMIN');
                })
                ->latest()
                ->get();
        }

        return view('templates.index', compact('templates'));
    }


    public function create()
    {
        return view('templates.editor'); // reuse your editor blade
    }

   public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'content' => 'required'
    ]);

    $template = Template::create([
        'user_id' => auth()->id(),
        'title'   => '',
        'name'    => $request->name,
        'content' => $request->content,
        'file_path' => null, // set below
    ]);

    // Always assign a stable storage path
    $diskPath = "previews/templates/{$template->id}.png";
    $template->update(['file_path' => $diskPath]);

    // Try to generate preview (this method is safe; it falls back to a GD placeholder)
    $this->generatePreviewImage($template->content, $diskPath);

    return redirect()->route('templates.index')->with('status', 'Template saved!');
}



   public function edit(Template $template)
    {
        $this->authorize('update', $template);
        return view('templates.editor', compact('template'));
    }




    public function useTemplate(Request $request, Template $template)
    {
        $useFor = $request->query('use_for');
        $documentId = $request->query('document_id');

        if ($useFor === 'chapter' && $documentId) {
            $document = ModelsDocument::findOrFail($documentId);

            // Save original content for undo
            session()->put('previousEditorContent', $document->content);

            // Persist template content (not flash)
            session()->put('templateContent', $template->content);

            return redirect()
                ->route('documents.edit', $documentId)
                ->with('status', 'Template applied! You can undo it.');
        }

        return view('documents.editor', ['template' => $template]);
    }








    

    public function update(Request $request, Template $template)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'content' => 'required'
    ]);

    $template->update([
        'name'    => $request->name,
        'content' => $request->content,
    ]);

    // Ensure file_path is set
    $diskPath = $template->file_path ?: "previews/templates/{$template->id}.png";
    if (!$template->file_path) {
        $template->update(['file_path' => $diskPath]);
    }

    // Clean old file (if any), then regenerate
    try {
        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory(dirname($diskPath));
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($diskPath)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($diskPath);
        }
    } catch (\Throwable $e) {
        // ignore
    }

    $this->generatePreviewImage($template->content, $diskPath);

    return redirect()->route('templates.index')->with('status', 'Template updated!');
}



  public function destroy($id)
{
    $template = Template::findOrFail($id);

    if ($template->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($template->file_path)) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($template->file_path);
    }

    $template->delete();

    return redirect()->route('templates.index')->with('status', 'Template deleted successfully.');
}




protected function generatePreviewImage(string $htmlContent, string $diskPath): void
{
    try {
        Storage::disk('public')->makeDirectory(dirname($diskPath));
    } catch (\Throwable $e) {}

    $target = storage_path("app/public/{$diskPath}");

    $fullHtml = "<html><head><meta charset='utf-8'><style>
        body{padding:20px;font-family:'Times New Roman', serif;font-size:14px;color:#111;}
        h1,h2,h3{margin:0 0 8px;font-weight:700;}
        p{margin:0 0 8px;}
    </style></head><body>{$htmlContent}</body></html>";

    try {
        $browsershot = Browsershot::html($fullHtml)
            ->windowSize(800, 1000)
            ->setOption('fullPage', true)
            ->setScreenshotType('png');

        $envChrome = getenv('CHROME_PATH') ?: null;
        $possiblePaths = array_filter([
            $envChrome,
            'C:\Program Files\Google\Chrome\Application\chrome.exe',
            'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
            'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
        ]);

        foreach ($possiblePaths as $path) {
            if ($path && is_file($path)) {
                $browsershot->setChromePath($path);
                break;
            }
        }

        $browsershot->save($target);
        if (is_file($target) && filesize($target) > 0) {
            return;
        }
    } catch (\Throwable $e) {
        // fall through
    }

    // Fallback placeholder via GD
    try {
        $w = 800; $h = 1000;
        $im = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($im, 245, 247, 250);
        $border = imagecolorallocate($im, 221, 226, 234);
        $txt = imagecolorallocate($im, 51, 65, 85);

        imagefilledrectangle($im, 0, 0, $w, $h, $bg);
        imagerectangle($im, 0, 0, $w-1, $h-1, $border);

        $message = "Preview unavailable on this server.\n(Chrome/Node not installed)";
        $y = 60;
        foreach (explode("\n", $message) as $line) {
            imagestring($im, 5, 40, $y, $line, $txt);
            $y += 24;
        }

        imagepng($im, $target);
        imagedestroy($im);
    } catch (\Throwable $e) {}
}





}
