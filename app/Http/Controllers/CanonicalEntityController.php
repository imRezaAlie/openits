<?php

namespace App\Http\Controllers;

use App\Models\CanonicalAttribute;
use App\Models\CanonicalEntity;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CanonicalEntityController extends Controller
{
    public function index(): View
    {
        $entities = CanonicalEntity::with('domain')
            ->withCount('attributes')
            ->orderBy('name')
            ->get();

        $domains = Domain::orderBy('name')->get();

        return view('data-dictionary.entities.index', compact('entities', 'domains'));
    }

    public function show(CanonicalEntity $canonicalEntity): View
    {
        $canonicalEntity->load(['domain', 'attributes']);

        return view('data-dictionary.entities.show', compact('canonicalEntity'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'domain_id' => 'nullable|exists:domains,id',
        ]);

        $validated['slug'] = $this->uniqueSlug(Str::slug($validated['name']));

        CanonicalEntity::create($validated);

        return redirect()
            ->route('data-dictionary.entities.index')
            ->with('success', 'Canonical entity created.');
    }

    public function update(Request $request, CanonicalEntity $canonicalEntity): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'domain_id' => 'nullable|exists:domains,id',
        ]);

        if ($validated['name'] !== $canonicalEntity->name) {
            $validated['slug'] = $this->uniqueSlug(Str::slug($validated['name']), $canonicalEntity->id);
        }

        $canonicalEntity->update($validated);

        return redirect()
            ->route('data-dictionary.entities.show', $canonicalEntity)
            ->with('success', 'Canonical entity updated.');
    }

    public function destroy(CanonicalEntity $canonicalEntity): RedirectResponse
    {
        $canonicalEntity->delete();

        return redirect()
            ->route('data-dictionary.entities.index')
            ->with('success', 'Canonical entity deleted.');
    }

    public function storeAttribute(Request $request, CanonicalEntity $canonicalEntity): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'data_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:2000',
            'is_required' => 'boolean',
        ]);

        $validated['slug'] = $this->uniqueAttributeSlug(
            $canonicalEntity,
            Str::slug($validated['name'])
        );
        $validated['is_required'] = $request->boolean('is_required');
        $validated['sort_order'] = $canonicalEntity->attributes()->max('sort_order') + 1;

        $canonicalEntity->attributes()->create($validated);

        return redirect()
            ->route('data-dictionary.entities.show', $canonicalEntity)
            ->with('success', 'Attribute added.');
    }

    public function destroyAttribute(CanonicalEntity $canonicalEntity, CanonicalAttribute $attribute): RedirectResponse
    {
        abort_unless($attribute->canonical_entity_id === $canonicalEntity->id, 404);

        $attribute->delete();

        return redirect()
            ->route('data-dictionary.entities.show', $canonicalEntity)
            ->with('success', 'Attribute removed.');
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $counter = 1;

        while (CanonicalEntity::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function uniqueAttributeSlug(CanonicalEntity $entity, string $base): string
    {
        $slug = $base;
        $counter = 1;

        while ($entity->attributes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
