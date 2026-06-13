<?php

namespace App\Http\Controllers;

use App\Models\PlatformField;
use App\Models\PlatformSchema;
use App\Models\System;
use App\Services\SchemaImportService;
use App\Support\DataLayers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformSchemaController extends Controller
{
    public function __construct(private SchemaImportService $importer) {}

    public function index(Request $request): View
    {
        $query = PlatformSchema::with('system')->withCount('fields');

        if ($request->filled('system_id')) {
            $query->where('system_id', $request->integer('system_id'));
        }

        if ($request->filled('data_layer')) {
            $query->where('data_layer', $request->string('data_layer'));
        }

        $schemas = $query->orderBy('name')->get();
        $systems = System::orderBy('name')->get();
        $layers = DataLayers::all();

        return view('platform-schemas.index', compact('schemas', 'systems', 'layers'));
    }

    public function show(PlatformSchema $platformSchema): View
    {
        $platformSchema->load(['system', 'fields.mappings.canonicalAttribute.entity']);

        return view('platform-schemas.show', compact('platformSchema'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'system_id' => 'required|exists:systems,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'data_layer' => ['required', Rule::in(DataLayers::all())],
            'source_type' => 'required|string|max:30',
            'version' => 'nullable|string|max:50',
        ]);

        $system = System::findOrFail($validated['system_id']);
        $validated['slug'] = $this->uniqueSlug($system, Str::slug($validated['name']));

        $schema = PlatformSchema::create($validated);

        return redirect()
            ->route('platform-schemas.show', $schema)
            ->with('success', 'Platform schema created.');
    }

    public function update(Request $request, PlatformSchema $platformSchema): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'data_layer' => ['required', Rule::in(DataLayers::all())],
            'source_type' => 'required|string|max:30',
            'version' => 'nullable|string|max:50',
        ]);

        if ($validated['name'] !== $platformSchema->name) {
            $validated['slug'] = $this->uniqueSlug(
                $platformSchema->system,
                Str::slug($validated['name']),
                $platformSchema->id
            );
        }

        $platformSchema->update($validated);

        return redirect()
            ->route('platform-schemas.show', $platformSchema)
            ->with('success', 'Platform schema updated.');
    }

    public function destroy(PlatformSchema $platformSchema): RedirectResponse
    {
        $platformSchema->delete();

        return redirect()
            ->route('platform-schemas.index')
            ->with('success', 'Platform schema deleted.');
    }

    public function storeField(Request $request, PlatformSchema $platformSchema): RedirectResponse
    {
        $validated = $request->validate([
            'native_name' => 'required|string|max:255',
            'native_path' => 'nullable|string|max:500',
            'data_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:2000',
            'is_primary_key' => 'boolean',
            'nullable' => 'boolean',
            'sample_value' => 'nullable|string|max:255',
        ]);

        $validated['is_primary_key'] = $request->boolean('is_primary_key');
        $validated['nullable'] = $request->boolean('nullable', true);
        $validated['sort_order'] = $platformSchema->fields()->max('sort_order') + 1;

        $platformSchema->fields()->create($validated);

        return redirect()
            ->route('platform-schemas.show', $platformSchema)
            ->with('success', 'Field added.');
    }

    public function destroyField(PlatformSchema $platformSchema, PlatformField $field): RedirectResponse
    {
        abort_unless($field->platform_schema_id === $platformSchema->id, 404);

        $field->delete();

        return redirect()
            ->route('platform-schemas.show', $platformSchema)
            ->with('success', 'Field removed.');
    }

    public function importFromSystem(System $system): RedirectResponse
    {
        $schemas = $this->importer->importFromSystem($system);

        if (empty($schemas)) {
            return redirect()
                ->route('platform-schemas.index', ['system_id' => $system->id])
                ->with('error', 'No REST API schemas found to import for this system.');
        }

        return redirect()
            ->route('platform-schemas.index', ['system_id' => $system->id])
            ->with('success', count($schemas).' schema(s) imported from APIs.');
    }

    private function uniqueSlug(System $system, string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $counter = 1;

        while (PlatformSchema::where('system_id', $system->id)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
