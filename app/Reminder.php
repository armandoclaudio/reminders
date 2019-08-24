<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $guarded = [];
    protected $dates = ['due_at'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
