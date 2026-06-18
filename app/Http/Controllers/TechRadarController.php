<?php

namespace App\Http\Controllers;

use App\Models\Technology;
use App\Models\TechnologyRadarEntry;
use App\Services\TechRadarService;
use App\Support\TechRadarRings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TechRadarController extends Controller
{
    public function __construct(
        private TechRadarService $radarService,
    ) {}

    public function index(): View
    {
        return view('c4.tech-radar.index', [
            'chartData' => $this->radarService->buildChartData(),
            'usageReport' => $this->radarService->usageReport(),
            'rings' => TechRadarRings::ALL,
        ]);
    }

    public function chartData(): JsonResponse
    {
        return response()->json($this->radarService->buildChartData());
    }

    public function updateEntry(Request $request, Technology $technology): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'ring' => TechRadarRings::validationRule(),
            'notes' => 'nullable|string|max:2000',
        ]);

        TechnologyRadarEntry::updateOrCreate(
            ['technology_id' => $technology->id],
            [
                'ring' => $data['ring'],
                'notes' => $data['notes'] ?? null,
                'updated_by' => $request->user()->id,
            ],
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Radar entry updated.']);
        }

        return back()->with('success', 'Technology radar position updated.');
    }
}
