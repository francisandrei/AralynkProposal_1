<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reports of inappropriate job posts
        Schema::create('job_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 500);
            $table->string('status', 20)->default('open');   // open, resolved, dismissed
            $table->timestamps();
        });

        // Simple history of actions
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60);
            $table->string('details', 255)->nullable();
            $table->timestamps();
        });

        // NEW: ratings. Both sides rate each other after a job is Completed.
        // rater_role "business" = a business rated a student, "student" = a student rated a business.
        // The unique key means one rating per person for each application.
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rater_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ratee_id')->constrained('users')->cascadeOnDelete();
            $table->string('rater_role', 20);
            $table->unsignedTinyInteger('stars');            // 1 to 5
            $table->string('comment', 500)->nullable();
            $table->timestamps();
            $table->unique(['application_id', 'rater_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('job_reports');
    }
};
