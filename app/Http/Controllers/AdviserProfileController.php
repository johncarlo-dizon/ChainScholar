<?php

namespace App\Http\Controllers;

use App\Models\AdviserProfile;
use App\Models\ResearchInterest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdviserProfileController extends Controller
{
    /**
     * Show the edit/create page for the signed-in adviser.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        abort_unless($user && $user->role === 'ADVISER', 403, 'Only advisers can access this page.');

        $profile = $user->adviserProfile()->with(['achievements', 'researchInterests'])->first();

        // Optional: provide interest suggestions to help the UI
        $suggestions = ResearchInterest::orderBy('name')->pluck('name')->take(50);

        return view('adviser.profile', [
            'user'        => $user,
            'profile'     => $profile,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Handle create/update of the adviser profile + achievements + interests.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'ADVISER', 403, 'Only advisers can update this.');

        // Validate
        $validated = $request->validate([
            'department'         => ['nullable','string','max:255'],
            'field_of_expertise' => ['nullable','string','max:255'],

            'highest_degree'     => ['nullable','string','max:100'],
            'degree_school'      => ['nullable','string','max:255'],
            'degree_year'        => ['nullable','integer','min:1900','max:'.(date('Y')+1)],

            'advisory_years'     => ['nullable','integer','min:0','max:200'],
            'projects_handled'   => ['nullable','integer','min:0','max:5000'],
            'notes'              => ['nullable','string'],

            // Achievements (repeater)
            'achievements'                 => ['nullable','array','max:20'],
            'achievements.*.title'         => ['required_with:achievements','string','max:255'],
            'achievements.*.issuer'        => ['nullable','string','max:255'],
            'achievements.*.year'          => ['nullable','integer','min:1900','max:'.(date('Y')+1)],
            'achievements.*.description'   => ['nullable','string','max:2000'],

            // Research interests (array of strings)
            'research_interests'           => ['nullable','array','max:30'],
            'research_interests.*'         => ['string','max:100'],
        ]);

        // Upsert profile
        $profile = AdviserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'department'         => $validated['department']         ?? null,
                'field_of_expertise' => $validated['field_of_expertise'] ?? null,

                'highest_degree'     => $validated['highest_degree']     ?? null,
                'degree_school'      => $validated['degree_school']      ?? null,
                'degree_year'        => $validated['degree_year']        ?? null,

                'advisory_years'     => $validated['advisory_years']     ?? null,
                'projects_handled'   => $validated['projects_handled']   ?? null,
                'notes'              => $validated['notes']              ?? null,
            ]
        );

        // Replace achievements with submitted list (skip entirely blank rows)
        if ($request->filled('achievements')) {
            $rows = collect($request->input('achievements', []))
                ->filter(function ($row) {
                    if (!is_array($row)) return false;
                    // keep if there's at least a non-empty title
                    return isset($row['title']) && trim($row['title']) !== '';
                })
                ->map(function ($row) {
                    return [
                        'title'       => trim($row['title'] ?? ''),
                        'issuer'      => $row['issuer'] ?? null,
                        'year'        => $row['year'] ?? null,
                        'description' => $row['description'] ?? null,
                    ];
                })
                ->values();

            // wipe + createMany for simplicity
            $profile->achievements()->delete();
            if ($rows->isNotEmpty()) {
                $profile->achievements()->createMany($rows->all());
            }
        } else {
            // If none submitted, remove existing achievements
            $profile->achievements()->delete();
        }

        // Sync research interests
        $interestNames = collect($request->input('research_interests', []))
            ->map(fn($n) => trim($n))
            ->filter()
            ->unique()
            ->values();

        $ids = [];
        foreach ($interestNames as $name) {
            $slug = Str::slug($name);
            $ids[] = ResearchInterest::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
        }
        $profile->researchInterests()->sync($ids);

        return back()->with('status', 'Adviser profile saved successfully.');
    }
}
