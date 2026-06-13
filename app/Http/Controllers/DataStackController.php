<?php

namespace App\Http\Controllers;

use App\Models\CanonicalEntity;
use App\Models\PlatformSchema;
use App\Models\System;
use App\Services\MappingCatalogService;
use App\Support\DataLayers;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DataStackController extends Controller
{
    public function __construct(private MappingCatalogService $catalog) {}

    public function index(): View
    {
        $stats = $this->catalog->stats();
        $layers = collect(DataLayers::all())->map(fn ($layer) => [
            'key' => $layer,
            'label' => DataLayers::label($layer),
            'description' => DataLayers::description($layer),
            'badge' => DataLayers::badgeClass($layer),
            'count' => $stats['by_layer'][$layer] ?? 0,
        ]);

        $entities = CanonicalEntity::withCount('attributes')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $schemas = PlatformSchema::with('system')
            ->withCount('fields')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $systemsWithSchemas = System::whereHas('platformSchemas')
            ->withCount('platformSchemas')
            ->orderBy('name')
            ->get();

        $recentMappings = $this->catalog->query()->take(10);

        return view('data-stack.index', compact(
            'stats',
            'layers',
            'entities',
            'schemas',
            'systemsWithSchemas',
            'recentMappings'
        ));
    }

    public function export(): JsonResponse
    {
        return response()->json($this->catalog->buildExport());
    }
}
