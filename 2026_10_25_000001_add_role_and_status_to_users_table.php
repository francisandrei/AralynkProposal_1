<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Laravel already creates a "users" table. We add two columns: role and status.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('student');    // student, business or admin
            $table->string('status', 20)->default('active');   // active, pending (business waiting for approval) or inactive
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status']);
        });
    }
};
