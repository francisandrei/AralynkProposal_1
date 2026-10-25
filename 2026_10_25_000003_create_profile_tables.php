<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One profile row for every student user and every business user
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name', 120);
            $table->string('student_no', 30)->unique();
            $table->string('contact_no', 20)->nullable();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->text('about')->nullable();
            $table->timestamps();
        });

        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name', 150);
            $table->string('contact_person', 120);
            $table->string('contact_no', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
        Schema::dropIfExists('student_profiles');
    }
};
