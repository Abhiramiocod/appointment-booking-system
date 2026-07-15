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
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable();
            $table->date('proposed_date')->nullable();
            $table->time('proposed_time')->nullable();
            $table->text('proposed_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'proposed_date', 'proposed_time', 'proposed_note']);
        });
    }
};
