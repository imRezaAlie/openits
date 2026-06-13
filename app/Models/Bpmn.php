<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $xml
 * @property int|null $project_id
 * @property int|null $system_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn whereXml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Bpmn withoutTrashed()
 * @mixin \Eloquent
 */
class Bpmn extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'diagram_type', 'project_id', 'system_id', 'xml'];

    public function isSequence(): bool
    {
        return $this->diagram_type === \App\Support\DiagramTypes::SEQUENCE;
    }

    public function isBpmn(): bool
    {
        return ! $this->isSequence();
    }

    public function editorUrl(): string
    {
        return $this->isSequence()
            ? route('systems.sequence.show', $this)
            : route('systems.bpmn.show', $this);
    }

    public function system()
    {
        return $this->belongsTo(System::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
