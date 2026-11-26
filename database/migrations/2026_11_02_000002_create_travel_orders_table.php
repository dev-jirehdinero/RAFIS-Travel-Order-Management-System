<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_id')->unique(); // e.g. TO-2026-13104
            $table->foreignId('personnel_id')->constrained('personnel')->cascadeOnDelete();
            $table->string('official_station');
            $table->text('google_drive_link')->nullable();
            $table->date('departure_date');
            $table->date('return_date');
            $table->string('destination');
            $table->string('status')->default('Not started')->index();
            $table->timestamps();

            $table->index('departure_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};
