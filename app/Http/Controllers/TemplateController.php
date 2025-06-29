<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class TemplateController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        if (auth()->check()) {
            $userId = auth()->id();

            // Get templates created by admins OR current user
            $templates = \App\Models\Template::whereHas('user', function ($query) use ($userId) {
                    $query->where('position', 'admin')
                        ->orWhere('id', $userId);
                })
                ->latest()
                ->get();
        } else {
            // If not logged in, show only templates created by admins
            $templates = \App\Models\Template::whereHas('user', function ($query) {
                    $query->where('position', 'admin');
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
            'title' => '',
            'name' => $request->name,
            'content' => $request->content
        ]);
        $this->generatePreviewImage($template->content, $template->id);

        return redirect()->route('templates.index')->with('success', 'Template saved!');
    }

    public function edit(Template $template)
    {
        $document = $template;
     
        return view('templates.editor', compact('document'));
    }


    public function useTemplate(Request $request, Template $template)
    {
        $useFor = $request->query('use_for');
        $documentId = $request->query('document_id');

        if ($useFor === 'chapter' && $documentId) {
            return redirect()
                ->route('documents.edit', ['document' => $documentId])
                ->with('templateContent', $template->content);
        }

        return view('documents.editor', ['template' => $template]); // fallback
    }




    

    public function update(Request $request, Template $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required'
        ]);

        $template->update([
            'name' => $request->name,
            'content' => $request->content
        ]);

        $this->generatePreviewImage($template->content, $template->id);

        return redirect()->route('templates.index')->with('success', 'Template updated!');
    }

    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully.');
    }


 protected function generatePreviewImage($htmlContent, $filename)
    {
        $fullHtml = "<html><head><style>body{padding:20px;font-family:'Times New Roman';}</style></head><body>{$htmlContent}</body></html>";

        $browsershot = Browsershot::html($fullHtml)
            ->windowSize(800, 1000)
            ->setOption('fullPage', true)
            ->setScreenshotType('png');

        // Dynamically pick browser path
        $possiblePaths = [
            'C:\Program Files\Google\Chrome\Application\chrome.exe',
            'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
            'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $browsershot->setChromePath($path);
                break;
            }
        }

        $browsershot->save(storage_path("app/public/previews/{$filename}.png"));
    }



}
