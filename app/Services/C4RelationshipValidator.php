<?php

namespace App\Services;

use App\Models\C4Relationship;
use App\Models\System;
use Illuminate\Support\Collection;
use RuntimeException;

class C4RelationshipValidator
{
    /**
     * @throws RuntimeException
     */
    public function validateNoCycle(string $sourceId, string $targetId, ?string $excludeId = null): void
    {
        if ($sourceId === $targetId) {
            throw new RuntimeException('A relationship cannot connect an element to itself.');
        }

        $graph = $this->buildAdjacencyList($excludeId);
        $graph[$sourceId][] = $targetId;

        if ($this->hasCycle($graph, $sourceId)) {
            throw new RuntimeException('This relationship would create a circular dependency.');
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private function buildAdjacencyList(?string $excludeId): array
    {
        $graph = [];

        $query = C4Relationship::query();
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        foreach ($query->get() as $rel) {
            $graph[$rel->source_id][] = $rel->target_id;
        }

        return $graph;
    }

    /**
     * @param  array<string, list<string>>  $graph
     */
    private function hasCycle(array $graph, string $start): bool
    {
        $visited = [];
        $stack = [];

        return $this->dfs($graph, $start, $visited, $stack);
    }

    /**
     * @param  array<string, list<string>>  $graph
     * @param  array<string, bool>  $visited
     * @param  array<string, bool>  $stack
     */
    private function dfs(array $graph, string $node, array &$visited, array &$stack): bool
    {
        $visited[$node] = true;
        $stack[$node] = true;

        foreach ($graph[$node] ?? [] as $neighbor) {
            if (! isset($visited[$neighbor])) {
                if ($this->dfs($graph, $neighbor, $visited, $stack)) {
                    return true;
                }
            } elseif (isset($stack[$neighbor])) {
                return true;
            }
        }

        unset($stack[$node]);

        return false;
    }
}
