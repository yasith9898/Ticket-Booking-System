<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardColumn extends Model
{
    protected $fillable = ['column_key', 'title', 'sort_order'];
}
