<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class ArchitecturalDecisionRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'system_id',
        'author_id',
        'title',
        'status',
        'context',
        'decision',
        'consequences',
        'decided_at',
        'reviewers',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'date',
            'reviewers' => 'array',
        ];
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function linkedElements(): BelongsToMany
    {
        return $this->belongsToMany(
            C4Container::class,
            'adr_c4_element',
            'adr_id',
            'element_id'
        )->withPivot('element_type')->withTimestamps();
    }

    /**
     * @param  list<array{element_id: string, element_type: string}>  $elements
     */
    public function syncLinkedElements(array $elements): void
    {
        DB::table('adr_c4_element')->where('adr_id', $this->id)->delete();

        foreach ($elements as $element) {
            DB::table('adr_c4_element')->insert([
                'adr_id' => $this->id,
                'element_id' => $element['element_id'],
                'element_type' => $element['element_type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @return list<array{element_id: string, element_type: string, name: string}>
     */
    public function resolvedLinkedElements(): array
    {
        $rows = DB::table('adr_c4_element')->where('adr_id', $this->id)->get();
        $resolved = [];

        foreach ($rows as $row) {
            $name = match ($row->element_type) {
                'container' => C4Container::find($row->element_id)?->name,
                'component' => C4Component::find($row->element_id)?->name,
                'context' => C4Context::find($row->element_id)?->name,
                default => null,
            };
            if ($name) {
                $resolved[] = [
                    'element_id' => $row->element_id,
                    'element_type' => $row->element_type,
                    'name' => $name,
                ];
            }
        }

        return $resolved;
    }
}
