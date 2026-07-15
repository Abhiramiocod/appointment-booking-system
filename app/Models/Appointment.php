<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'customer_id',
        'staff_id',
        'service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'rejection_reason',
        'proposed_date',
        'proposed_time',
        'proposed_note',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'proposed_date' => 'date',
            'status' => AppointmentStatus::class,
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(StaffReview::class);
    }
}
