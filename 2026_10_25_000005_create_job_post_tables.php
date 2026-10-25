<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Job postings. The table is called "job_posts" because Laravel already uses the name "jobs" for its own queue.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained();
            $table->string('title', 150);
            $table->text('description');
            $table->string('job_type', 20);                 // 1-Day, Several-Day, Recurring, Part-Time
            $table->string('work_location', 255);
            $table->date('work_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('slots')->default(1);
            $table->string('required_experience', 255)->nullable();
            $table->text('additional_requirements')->nullable();
            $table->string('compensation', 100)->nullable();   // text only, Aralynk never handles payment
            $table->date('deadline');
            $table->string('status', 20)->default('open');     // open, closed, completed
            $table->boolean('is_hidden')->default(false);      // the admin can hide a post
            $table->timestamps();
        });

        Schema::create('job_post_skill', function (Blueprint $table) {
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['job_post_id', 'skill_id']);
        });

        Schema::create('job_post_course', function (Blueprint $table) {
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->primary(['job_post_id', 'course_id']);
        });

        // Weekdays of Recurring and Part-Time jobs
        Schema::create('job_post_days', function (Blueprint $table) {
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->primary(['job_post_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_post_days');
        Schema::dropIfExists('job_post_course');
        Schema::dropIfExists('job_post_skill');
        Schema::dropIfExists('job_posts');
    }
};
