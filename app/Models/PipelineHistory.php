<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipelineHistory extends Model
{
    protected $fillable = ['pipeline_card_id', 'stage_name', 'note', 'happened_on'];

    protected function casts(): array
    {
        return ['happened_on' => 'date'];
    }

    public function pipelineCard(): BelongsTo
    {
        return $this->belongsTo(PipelineCard::class);
    }
}
