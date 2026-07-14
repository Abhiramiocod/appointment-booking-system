<?php

namespace App\Models;

use App\Enums\StaffApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffApplication extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'designation_id',
        'cover_letter',
        'experience_years',
        'admin_notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'status' => StaffApplicationStatus::class,
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
