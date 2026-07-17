<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $fillable = [
        'staff_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'breaks',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'breaks' => 'array',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
