<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reminder extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['due_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
