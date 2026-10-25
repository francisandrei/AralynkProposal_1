<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Lists that are filled by seeders: courses, categories and skills
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 200);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->boolean('is_approved')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('courses');
    }
};
