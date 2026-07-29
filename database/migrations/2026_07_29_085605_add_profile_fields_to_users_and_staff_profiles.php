<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->date('dob')->nullable()->after('phone');
            $table->string('gender')->nullable()->after('dob');
            $table->string('address')->nullable()->after('gender');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('postal_code')->nullable()->after('country');
            $table->text('bio')->nullable()->after('postal_code');

            // Customer specific
            $table->string('preferred_contact_method')->default('email')->after('bio');
            $table->string('emergency_contact')->nullable()->after('preferred_contact_method');
            $table->text('medical_notes')->nullable()->after('emergency_contact');
            $table->string('preferred_language')->default('English')->after('medical_notes');
        });

        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('specialization')->nullable()->after('experience_years');
            $table->string('license_number')->nullable()->after('specialization');
            $table->date('working_since')->nullable()->after('license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'dob', 'gender', 'address', 'city', 'state', 'country',
                'postal_code', 'bio', 'preferred_contact_method', 'emergency_contact',
                'medical_notes', 'preferred_language',
            ]);
        });

        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropColumn(['specialization', 'license_number', 'working_since']);
        });
    }
};
