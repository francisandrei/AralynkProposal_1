<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

// The 17 BASC programs that students can choose from
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['DVM', 'Doctor of Veterinary Medicine (DVM)'],
            ['BSA-AnSci', 'Bachelor of Science in Agriculture (BSA) - Animal Science'],
            ['BSA-AgExt', 'Bachelor of Science in Agriculture (BSA) - Agricultural Extension and Communication'],
            ['BSA-Hort', 'Bachelor of Science in Agriculture (BSA) - Crop Science, specializing in Horticulture'],
            ['BSA-Agro', 'Bachelor of Science in Agriculture (BSA) - Crop Science, specializing in Agronomy'],
            ['BSABE', 'Bachelor of Science in Agricultural and Biosystems Engineering'],
            ['BSGE', 'Bachelor of Science in Geodetic Engineering'],
            ['BSFT', 'Bachelor of Science in Food Technology'],
            ['BSIT', 'Bachelor of Science in Information Technology (BSIT)'],
            ['BSAgribiz', 'Bachelor of Science in Agribusiness'],
            ['BSBA-Mktg', 'Bachelor of Science in Business Administration (BSBA) - Marketing Management'],
            ['BSHM', 'Bachelor of Science in Hospitality Management (BSHM)'],
            ['BEEd', 'Bachelor of Elementary Education (BEEd)'],
            ['BSEd-Eng', 'Bachelor of Secondary Education (BSEd) - English'],
            ['BSEd-Sci', 'Bachelor of Secondary Education (BSEd) - Science'],
            ['BSEd-Math', 'Bachelor of Secondary Education (BSEd) - Mathematics'],
            ['BSDevCom', 'Bachelor of Science in Development Communication'],
        ];

        foreach ($courses as $item) {
            Course::updateOrCreate(['code' => $item[0]], ['name' => $item[1]]);
        }
    }
}
