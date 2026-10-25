<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Skills, experience and availability of a student
return new class extends Migration
{
    public function up(): void
    {
        // student <-> skill (many to many)
        Schema::create('student_skills', function (Blueprint $table) {
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['student_profile_id', 'skill_id']);
        });

        Schema::create('student_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });

        // Weekly availability: one row per weekday (1 = Monday ... 7 = Sunday). No row = unavailable.
        Schema::create('student_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->unique(['student_profile_id', 'day_of_week']);
        });

        // "Available for this day": one specific date
        Schema::create('student_day_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->date('avail_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unique(['student_profile_id', 'avail_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_day_availabilities');
        Schema::dropIfExists('student_availabilities');
        Schema::dropIfExists('student_experiences');
        Schema::dropIfExists('student_skills');
    }
};
