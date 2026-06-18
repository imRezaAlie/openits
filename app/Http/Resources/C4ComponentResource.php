<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\C4Component */
class C4ComponentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'c4_container_id' => $this->c4_container_id,
            'name' => $this->name,
            'type' => $this->type,
            'type_label' => \App\Support\C4ComponentTypes::label($this->type),
            'technology' => $this->technology,
            'description' => $this->description,
            'dependencies' => $this->dependencies ?? [],
            'position' => $this->position,
            'metadata' => $this->metadata,
            'sunset_date' => $this->sunset_date?->toDateString(),
            'deprecated' => $this->isDeprecated(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
