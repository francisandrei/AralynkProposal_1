<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A student applies to a job. The unique key stops duplicate applications.
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('Pending');   // Pending, Reviewed, Accepted, Rejected, Withdrawn, Completed
            $table->timestamps();
            $table->unique(['student_profile_id', 'job_post_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_post_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('message', 255);
            $table->string('link', 255)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('applications');
    }
};
