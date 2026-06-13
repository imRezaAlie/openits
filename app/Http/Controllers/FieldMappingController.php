<?php

namespace App\Http\Controllers;

use App\Models\CanonicalAttribute;
use App\Models\FieldMapping;
use App\Models\PlatformField;
use App\Models\System;
use App\Services\MappingCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FieldMappingController extends Controller
{
    public function __construct(private MappingCatalogService $catalog) {}

    public function index(Request $request): View
    {
        $mappings = $this->catalog->query(
            $request->integer('system_id') ?: null,
            $request->integer('entity_id') ?: null
        );

        $systems = System::orderBy('name')->get();
        $attributes = CanonicalAttribute::with('entity')->orderBy('name')->get();

        return view('field-mappings.index', compact('mappings', 'systems', 'attributes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform_field_id' => 'required|exists:platform_fields,id',
            'canonical_attribute_id' => 'required|exists:canonical_attributes,id',
            'api_version_id' => 'nullable|exists:api_versions,id',
            'direction' => ['required', Rule::in([
                FieldMapping::DIRECTION_INBOUND,
                FieldMapping::DIRECTION_OUTBOUND,
                FieldMapping::DIRECTION_BIDIRECTIONAL,
            ])],
            'transform_rule' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        FieldMapping::create($validated);

        return redirect()
            ->route('field-mappings.index')
            ->with('success', 'Field mapping created.');
    }

    public function destroy(FieldMapping $fieldMapping): RedirectResponse
    {
        $fieldMapping->delete();

        return redirect()
            ->route('field-mappings.index')
            ->with('success', 'Field mapping deleted.');
    }
}
