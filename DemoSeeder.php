<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Course;
use App\Models\JobPost;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Optional sample data. Run with:  php artisan db:seed --class=DemoSeeder
// Every demo account uses the password Demo@1234
class DemoSeeder extends Seeder
{
    // today + $days, as a date like 2026-11-05 (null stays null)
    private function day($days)
    {
        if ($days === null) return null;
        return now()->addDays($days)->toDateString();
    }

    public function run(): void
    {
        $password = Hash::make('Demo@1234');

        // ----- Businesses (already approved) -----
        $businesses = [
            // email, business name, contact person, category, address
            ['rosa@demo.test', 'Kusina ni Aling Rosa', 'Rosa Mendoza', 'Food / Restaurant', 'Poblacion, San Ildefonso, Bulacan'],
            ['techhub@demo.test', 'San Ildefonso Tech Hub', 'Carlo Bautista', 'IT / Technology', 'Gabihan, San Ildefonso, Bulacan'],
            ['farm@demo.test', 'Bulacan Farm Fresh', 'Pedro Cruz', 'Agriculture / Farm', 'Malipampang, San Ildefonso, Bulacan'],
        ];
        $profiles = [];
        foreach ($businesses as $b) {
            $user = User::create(['name' => $b[1], 'email' => $b[0], 'password' => $password, 'role' => 'business', 'status' => 'active']);
            $profiles[$b[0]] = BusinessProfile::create([
                'user_id' => $user->id, 'business_name' => $b[1], 'contact_person' => $b[2], 'contact_no' => '09171234567',
                'address' => $b[4], 'category_id' => Category::where('name', $b[3])->value('id'), 'description' => 'Demo business: ' . $b[1],
            ]);
        }

        // ----- Students -----
        $students = [
            // email, name, student no, course code, year, skills, experience [title, category], weekly availability [day => [start, end]]
            ['maria@demo.test', 'Maria Santos', '2023-0001', 'BSIT', 3, ['PHP', 'HTML/CSS', 'MySQL', 'MS Word'], [],
                [1 => ['13:00', '18:00'], 3 => ['13:00', '18:00'], 5 => ['13:00', '18:00'], 6 => ['08:00', '17:00']]],
            ['juan@demo.test', 'Juan Dela Cruz', '2023-0002', 'BSBA-Mktg', 2, ['Customer Service', 'Cashiering', 'Communication'],
                [['Part-time cashier at a grocery', 'Food / Restaurant']], [3 => ['13:00', '18:00'], 6 => ['08:00', '18:00'], 7 => ['08:00', '18:00']]],
            ['ana@demo.test', 'Ana Reyes', '2022-0105', 'BSEd-Eng', 4, ['Tutoring', 'Communication', 'Content Writing'],
                [['Reading tutor for neighbors', 'Tutoring / Education']], [2 => ['15:00', '19:00'], 4 => ['15:00', '19:00']]],
            ['mark@demo.test', 'Mark Villanueva', '2024-0210', 'BSHM', 1, ['Cooking / Food Prep', 'Customer Service'], [],
                [6 => ['07:00', '18:00'], 7 => ['07:00', '18:00']]],
        ];
        $studentProfiles = [];
        foreach ($students as $s) {
            $user = User::create(['name' => $s[1], 'email' => $s[0], 'password' => $password, 'role' => 'student', 'status' => 'active']);
            $profile = StudentProfile::create([
                'user_id' => $user->id, 'full_name' => $s[1], 'student_no' => $s[2], 'contact_no' => '09181234567',
                'course_id' => Course::where('code', $s[3])->value('id'), 'year_level' => $s[4], 'about' => 'Hardworking BASC student looking for part-time work.',
            ]);
            $profile->skills()->attach(Skill::whereIn('name', $s[5])->pluck('id')->toArray());
            foreach ($s[6] as $exp) {
                $profile->experiences()->create(['title' => $exp[0], 'category_id' => Category::where('name', $exp[1])->value('id')]);
            }
            foreach ($s[7] as $dayNumber => $times) {
                $profile->availabilities()->create(['day_of_week' => $dayNumber, 'start_time' => $times[0], 'end_time' => $times[1]]);
            }
            $studentProfiles[$s[0]] = $profile;
        }

        // ----- Jobs -----
        $jobs = [
            ['biz' => 'rosa@demo.test', 'category' => 'Food / Restaurant', 'title' => 'Weekend Cashier', 'type' => 'Part-Time',
             'from' => null, 'to' => null, 'start' => '09:00', 'end' => '17:00', 'slots' => 2, 'deadline' => 10,
             'skills' => ['Cashiering', 'Customer Service'], 'courses' => ['BSBA-Mktg', 'BSHM'], 'days' => [6, 7]],
            ['biz' => 'rosa@demo.test', 'category' => 'Events', 'title' => 'Fiesta Catering Helper (1 day)', 'type' => '1-Day',
             'from' => 14, 'to' => null, 'start' => '08:00', 'end' => '16:00', 'slots' => 4, 'deadline' => 10,
             'skills' => ['Event Assistance', 'Cooking / Food Prep'], 'courses' => [], 'days' => []],
            ['biz' => 'techhub@demo.test', 'category' => 'IT / Technology', 'title' => 'Junior Web Developer Assistant', 'type' => 'Recurring',
             'from' => 5, 'to' => null, 'start' => '13:00', 'end' => '17:00', 'slots' => 1, 'deadline' => 4,
             'skills' => ['PHP', 'HTML/CSS', 'MySQL'], 'courses' => ['BSIT'], 'days' => [1, 3, 5]],
            ['biz' => 'techhub@demo.test', 'category' => 'Research / Data Encoding', 'title' => 'Data Encoder (3 days)', 'type' => 'Several-Day',
             'from' => 8, 'to' => 10, 'start' => '09:00', 'end' => '15:00', 'slots' => 2, 'deadline' => 6,
             'skills' => ['Data Encoding', 'MS Excel'], 'courses' => [], 'days' => []],
            ['biz' => 'techhub@demo.test', 'category' => 'Social Media / Content', 'title' => 'Social Media Content Creator', 'type' => 'Part-Time',
             'from' => null, 'to' => null, 'start' => '14:00', 'end' => '18:00', 'slots' => 1, 'deadline' => 15,
             'skills' => ['Social Media Management', 'Content Writing'], 'courses' => [], 'days' => [2, 4]],
            ['biz' => 'farm@demo.test', 'category' => 'Agriculture / Farm', 'title' => 'Harvest Season Farm Helper', 'type' => 'Several-Day',
             'from' => 9, 'to' => 12, 'start' => '06:00', 'end' => '11:00', 'slots' => 6, 'deadline' => 7,
             'skills' => ['Farm Work'], 'courses' => ['BSA-Agro', 'BSA-Hort', 'BSAgribiz'], 'days' => []],
            ['biz' => 'farm@demo.test', 'category' => 'Tutoring / Education', 'title' => 'Math Tutor for Grade 6', 'type' => 'Recurring',
             'from' => 4, 'to' => null, 'start' => '16:00', 'end' => '18:00', 'slots' => 1, 'deadline' => 3,
             'skills' => ['Tutoring', 'Communication'], 'courses' => ['BSEd-Math', 'BEEd'], 'days' => [2, 4]],
            // a finished job, so ratings can be tested right away
            ['biz' => 'rosa@demo.test', 'category' => 'Events', 'title' => 'Barangay Event Helper (finished)', 'type' => '1-Day',
             'from' => -10, 'to' => null, 'start' => '08:00', 'end' => '16:00', 'slots' => 2, 'deadline' => -14,
             'skills' => ['Event Assistance'], 'courses' => [], 'days' => [], 'status' => 'completed'],
        ];
        $jobModels = [];
        foreach ($jobs as $j) {
            $status = 'open';
            if (isset($j['status'])) $status = $j['status'];
            $job = JobPost::create([
                'business_profile_id' => $profiles[$j['biz']]->id,
                'category_id' => Category::where('name', $j['category'])->value('id'),
                'title' => $j['title'], 'description' => 'Demo job: ' . $j['title'] . '. Bring a valid ID and report on time.',
                'job_type' => $j['type'], 'work_location' => 'San Ildefonso, Bulacan',
                'work_date' => $this->day($j['from']), 'end_date' => $this->day($j['to']),
                'start_time' => $j['start'], 'end_time' => $j['end'], 'slots' => $j['slots'],
                'compensation' => 'PHP 450 per day', 'deadline' => $this->day($j['deadline']), 'status' => $status,
            ]);
            $job->skills()->attach(Skill::whereIn('name', $j['skills'])->pluck('id')->toArray());
            $job->courses()->attach(Course::whereIn('code', $j['courses'])->pluck('id')->toArray());
            foreach ($j['days'] as $dayNumber) {
                DB::table('job_post_days')->insert(['job_post_id' => $job->id, 'day_of_week' => $dayNumber]);
            }
            $jobModels[$j['title']] = $job;
        }

        // ----- A few applications -----
        $applications = [
            ['juan@demo.test', 'Weekend Cashier', 'Reviewed'],
            ['mark@demo.test', 'Weekend Cashier', 'Pending'],
            ['maria@demo.test', 'Junior Web Developer Assistant', 'Accepted'],
            ['ana@demo.test', 'Math Tutor for Grade 6', 'Pending'],
            ['juan@demo.test', 'Barangay Event Helper (finished)', 'Completed'],
            ['mark@demo.test', 'Barangay Event Helper (finished)', 'Completed'],
        ];
        foreach ($applications as $a) {
            Application::create([
                'student_profile_id' => $studentProfiles[$a[0]]->id,
                'job_post_id' => $jobModels[$a[1]]->id,
                'message' => 'I am interested in this job.',
                'status' => $a[2],
            ]);
        }
    }
}
