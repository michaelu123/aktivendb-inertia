<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';

    protected $fillable = [
        'reference_table',
        'reference_id',
        'user_id',
        'record_old',
        'record_new'
    ];

    // Relationships

    public function user()
    {
        return $this->hasOne('App\Models\User');
    }
}
