<?php

namespace App\Services;

use App\Models\Api;
use App\Models\TpsMetric;
use Illuminate\Support\Collection;

class TpsService
{
    public function record(Api $api, float $tpsValue, ?string $notes = null, ?\DateTimeInterface $recordedAt = null): TpsMetric
    {
        return TpsMetric::create([
            'api_id' => $api->id,
            'tps_value' => $tpsValue,
            'recorded_at' => $recordedAt ?? now(),
            'notes' => $notes,
        ]);
    }

    public function getHistory(Api $api, int $limit = 50): Collection
    {
        return $api->tpsMetrics()
            ->orderByDesc('recorded_at')
            ->limit($limit)
            ->get();
    }

    public function getChartData(Api $api, int $limit = 30): array
    {
        $metrics = $api->tpsMetrics()
            ->orderBy('recorded_at')
            ->limit($limit)
            ->get();

        return [
            'labels' => $metrics->map(fn ($m) => $m->recorded_at->format('M d, H:i'))->values()->all(),
            'values' => $metrics->pluck('tps_value')->values()->all(),
        ];
    }

    public function getCurrentTps(Api $api): ?float
    {
        $latest = $api->tpsMetrics()->orderByDesc('recorded_at')->first();

        return $latest?->tps_value;
    }
}
