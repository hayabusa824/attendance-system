<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_date',
        'reason',
        'applied_date',
        'status',
        'clock_in',
        'clock_out',
        'break_in',
        'break_out'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
