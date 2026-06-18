<?php

namespace App\Services;

use App\Models\Technology;
use App\Support\TechRadarRings;
use App\Support\TechnologyCategories;
use Illuminate\Support\Collection;

class TechRadarService
{
    /**
     * @return array<string, mixed>
     */
    public function buildChartData(): array
    {
        $technologies = Technology::with(['radarEntry', 'systems'])->orderBy('name')->get();

        $blips = [];
        $quadrantIndex = [];

        foreach (TechnologyCategories::ALL as $i => $category) {
            $quadrantIndex[$category] = $i;
        }

        $quadrantCounts = array_fill(0, count(TechnologyCategories::ALL), 0);

        foreach ($technologies as $tech) {
            $category = $tech->category;
            $q = $quadrantIndex[$category] ?? 0;
            $ring = $tech->radarEntry?->ring ?? TechRadarRings::ASSESS;
            $offset = $quadrantCounts[$q]++;
            $angle = $this->angleForBlip($q, $offset, max(1, $technologies->where('category', $category)->count()));

            $blips[] = [
                'id' => $tech->id,
                'name' => $tech->name,
                'category' => $category,
                'category_label' => TechnologyCategories::label($category),
                'ring' => $ring,
                'ring_label' => TechRadarRings::label($ring),
                'radius' => TechRadarRings::RADIUS[$ring] ?? 0.65,
                'angle' => $angle,
                'quadrant' => $q,
                'systems_count' => $tech->systems->count(),
                'notes' => $tech->radarEntry?->notes,
            ];
        }

        return [
            'rings' => TechRadarRings::ALL,
            'ring_labels' => TechRadarRings::LABELS,
            'quadrants' => array_values(TechnologyCategories::LABELS),
            'blips' => $blips,
        ];
    }

    private function angleForBlip(int $quadrant, int $offset, int $totalInQuadrant): float
    {
        $sector = (2 * M_PI) / count(TechnologyCategories::ALL);
        $start = $quadrant * $sector - M_PI / 2;
        $step = $sector / max($totalInQuadrant + 1, 2);

        return $start + $step * ($offset + 1);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function usageReport(): Collection
    {
        return Technology::withCount('systems')
            ->with('radarEntry')
            ->orderByDesc('systems_count')
            ->get()
            ->map(fn ($t) => [
                'name' => $t->name,
                'category' => TechnologyCategories::label($t->category),
                'ring' => TechRadarRings::label($t->radarEntry?->ring ?? TechRadarRings::ASSESS),
                'systems_count' => $t->systems_count,
            ]);
    }
}
