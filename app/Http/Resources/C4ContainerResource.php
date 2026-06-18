<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\C4Container */
class C4ContainerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'system_id' => $this->system_id,
            'name' => $this->name,
            'type' => $this->type,
            'type_label' => \App\Support\C4ContainerTypes::label($this->type),
            'technology' => $this->technology,
            'description' => $this->description,
            'position' => $this->position,
            'metadata' => $this->metadata,
            'sunset_date' => $this->sunset_date?->toDateString(),
            'deprecated' => $this->isDeprecated(),
            'components' => C4ComponentResource::collection($this->whenLoaded('components')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
