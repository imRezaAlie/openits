<?php

namespace App\Services;

use App\Models\C4Context;
use App\Models\System;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class C4ContextElementService
{
    public function ensureElementUuids(C4Context $context): C4Context
    {
        $dirty = false;
        $externals = $context->external_systems ?? [];

        foreach ($externals as $index => $external) {
            if (! $this->isUuid($external['id'] ?? null)) {
                $externals[$index]['id'] = (string) Str::uuid();
                $dirty = true;
            }
        }

        $users = $context->users ?? [];

        foreach ($users as $index => $user) {
            if (! $this->isUuid($user['id'] ?? null)) {
                $users[$index]['id'] = (string) Str::uuid();
                $dirty = true;
            }
        }

        if ($dirty) {
            $context->update([
                'external_systems' => $externals,
                'users' => $users,
            ]);
            $context->refresh();
        }

        return $context;
    }

    public function resolveRelationshipId(System $system, string $rawId, string $field = 'source_id'): string
    {
        if ($this->isUuid($rawId)) {
            return $rawId;
        }

        $context = $system->c4Context;
        if (! $context) {
            throw ValidationException::withMessages([
                $field => 'C4 context not found for this system.',
            ]);
        }

        if ($rawId === 'system-'.$system->id || $rawId === (string) $system->id) {
            return $context->id;
        }

        if (str_starts_with($rawId, 'boundary-')) {
            throw ValidationException::withMessages([
                $field => 'System boundaries cannot be connection endpoints.',
            ]);
        }

        foreach ($context->external_systems ?? [] as $index => $external) {
            $legacyId = $external['id'] ?? 'external-'.$index;
            if ($rawId === $legacyId || $rawId === 'external-'.$index) {
                $context = $this->ensureElementUuids($context);

                return $context->external_systems[$index]['id'];
            }
        }

        foreach ($context->users ?? [] as $index => $user) {
            $legacyId = $user['id'] ?? 'user-'.$index;
            if ($rawId === $legacyId || $rawId === 'user-'.$index) {
                $context = $this->ensureElementUuids($context);

                return $context->users[$index]['id'];
            }
        }

        $system->loadMissing('c4Containers');
        if ($system->c4Containers->contains('id', $rawId)) {
            return $rawId;
        }

        throw ValidationException::withMessages([
            $field => 'Unknown diagram element id: '.$rawId,
        ]);
    }

    /**
     * @return list<string>
     */
    public function contextRelationshipIds(System $system, ?C4Context $context = null): array
    {
        $context = $context ?? $system->c4Context;
        if (! $context) {
            return [];
        }

        $context = $this->ensureElementUuids($context);
        $ids = [$context->id, 'system-'.$system->id];

        foreach ($context->external_systems ?? [] as $external) {
            $ids[] = $external['id'];
        }

        foreach ($context->users ?? [] as $user) {
            $ids[] = $user['id'];
        }

        return array_values(array_unique($ids));
    }

    private function isUuid(?string $value): bool
    {
        return is_string($value) && Str::isUuid($value);
    }
}
