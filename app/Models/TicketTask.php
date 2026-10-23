<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketTask extends Model
{
    protected $fillable = ['ticket_id', 'title', 'completed', 'sort_order'];

    protected function casts(): array
    {
        return ['completed' => 'boolean'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
