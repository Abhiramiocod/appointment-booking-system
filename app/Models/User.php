<?php

namespace App\Models;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Notifications\CustomResetPasswordNotification;
use App\Notifications\CustomVerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmailNotification);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'image',
        'provider',
        'provider_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'provider' => AuthProvider::class,
        ];
    }


    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::STAFF;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    public function profile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_staff', 'staff_id', 'service_id');
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function workingHours()
    {
        return $this->hasMany(WorkingHour::class, 'staff_id');
    }

    public function customerAppointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function staffAppointments()
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }

    public function receivedReviews(): HasMany
    {
        return $this->hasMany(StaffReview::class, 'staff_id');
    }

    public function givenReviews(): HasMany
    {
        return $this->hasMany(StaffReview::class, 'customer_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
