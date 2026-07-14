<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'bio',
        'designation_id',
        'experience_years',
        'employment_status',
        'profile_photo',
    ];

    protected $casts = [
        'employment_status' => EmploymentStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }
}
