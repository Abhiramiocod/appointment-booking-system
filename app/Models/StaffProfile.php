<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'bio',
        'experience_years',
        'profile_photo',
    ];

    protected $casts = [
    'employment_status' => EmploymentStatus::class,
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
