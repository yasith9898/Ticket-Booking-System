<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineCard extends Model
{
    protected $fillable = ['client_id', 'column_key', 'sales_rep', 'sort_order'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PipelineHistory::class)->orderByDesc('happened_on')->orderByDesc('id');
    }
}
