<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    public static function nextNumber(): int
    {
        $max = static::max('ticket_number');

        return $max ? ((int) $max + 1) : 1001;
    }

    protected $fillable = [
        'ticket_number',
        'subject',
        'client_name',
        'company',
        'mobile',
        'client_email',
        'employee',
        'sales_rep',
        'status',
        'priority',
        'opened_on',
        'due_on',
        'public_comment',
        'internal_note',
        'workflow_step',
        'payment_ok',
        'product',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'opened_on' => 'date',
            'due_on' => 'date',
            'payment_ok' => 'boolean',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TicketTask::class)->orderBy('sort_order')->orderBy('id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
