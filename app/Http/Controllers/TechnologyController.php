<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Models\Technology;
use App\Support\TechnologyCategories;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TechnologyController extends Controller
{
    public function catalog(): View
    {
        $technologiesByCategory = Technology::withCount('systems')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $categories = TechnologyCategories::ALL;

        return view('technologies.index', compact('technologiesByCategory', 'categories'));
    }

    public function show(Technology $technology): View
    {
        $technology->load(['systems' => fn ($q) => $q->with('vendor')->orderBy('name')]);

        return view('technologies.show', compact('technology'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('technologies')->where(fn ($q) => $q->where('category', $request->input('category'))),
            ],
            'category' => TechnologyCategories::validationRule(),
            'icon' => 'nullable|string|max:255',
        ]);

        Technology::create($validated);

        return redirect()
            ->route('technologies.index')
            ->with('success', 'Technology added to catalog.');
    }

    public function update(Request $request, Technology $technology): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('technologies')
                    ->where(fn ($q) => $q->where('category', $request->input('category')))
                    ->ignore($technology->id),
            ],
            'category' => TechnologyCategories::validationRule(),
            'icon' => 'nullable|string|max:255',
        ]);

        $technology->update($validated);

        return redirect()
            ->route('technologies.index')
            ->with('success', 'Technology updated successfully.');
    }

    public function destroy(Technology $technology): RedirectResponse
    {
        $technology->delete();

        return redirect()
            ->route('technologies.index')
            ->with('success', 'Technology removed from catalog.');
    }

    public function index(System $system): View
    {
        $system->load(['vendor', 'parent', 'technologies']);

        $technologiesByCategory = Technology::orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $assigned = $system->technologies->keyBy('id');

        return view('systems.technologies', compact(
            'system',
            'technologiesByCategory',
            'assigned',
        ));
    }

    public function sync(Request $request, System $system): RedirectResponse
    {
        $validated = $request->validate([
            'technologies' => 'nullable|array',
            'technologies.*.id' => 'required|exists:technologies,id',
            'technologies.*.version' => 'nullable|string|max:50',
        ]);

        $syncData = [];
        foreach ($validated['technologies'] ?? [] as $item) {
            $syncData[(int) $item['id']] = ['version' => $item['version'] ?? null];
        }

        $system->technologies()->sync($syncData);

        return redirect()
            ->route('systems.technologies', $system)
            ->with('success', 'Tech stack updated successfully.');
    }
}
