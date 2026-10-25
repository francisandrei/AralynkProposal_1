<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// php artisan db:seed runs this file
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CourseSeeder::class,
            CategorySeeder::class,
            SkillSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
