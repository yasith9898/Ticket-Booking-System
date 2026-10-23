<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'person_name',
        'company',
        'mobile',
        'category',
        'city',
        'address',
        'sales_rep',
        'email',
    ];

    public function pipelineCards(): HasMany
    {
        return $this->hasMany(PipelineCard::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
