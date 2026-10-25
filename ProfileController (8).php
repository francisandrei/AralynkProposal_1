<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Skill;
use App\Models\StudentDayAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Everything on the student's "My profile" page. Each form on the page has its own method.
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $student = $user->studentProfile;

        $data = [
            'student' => $student,
            'email' => $user->email,
            'completion' => $student->completion(),
            'courses' => Course::orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'allSkills' => Skill::where('is_approved', true)->orderBy('name')->get(),
            'mySkillIds' => $student->skills()->pluck('skills.id')->toArray(),
            'experiences' => $student->experiences()->with('category')->get(),
            'weekly' => $student->availabilities()->get()->keyBy('day_of_week'),
            'specificDays' => $student->dayAvailabilities()->where('avail_date', '>=', now()->toDateString())->orderBy('avail_date')->get(),
            'ratingAverage' => $user->averageRating(),
            'ratingCount' => $user->ratingsReceived()->count(),
            'ratings' => $user->ratingsReceived()->with(['rater.businessProfile', 'application.jobPost'])->orderByDesc('id')->limit(5)->get(),
        ];

        return view('student.profile', $data);
    }

    // ----- Basic information -----
    public function update(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|min:3|max:120',
            'contact_no' => ['nullable', 'regex:/^[0-9+\- ]{7,20}$/'],
            'course_id' => 'required|exists:courses,id',
            'year_level' => 'required|integer|between:1,5',
            'about' => 'nullable|string|max:1000',
        ]);

        $request->user()->studentProfile->update($data);
        $request->user()->update(['name' => $data['full_name']]);

        return back()->with('success', 'Profile updated.');
    }

    // ----- Skills: the ticked skills plus "other skills" typed by the student -----
    public function saveSkills(Request $request)
    {
        $request->validate([
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
            'other_skills' => 'nullable|string|max:300',
        ]);

        $ids = $request->input('skills', []);
        $ids = array_merge($ids, Skill::addFromText($request->input('other_skills')));

        // sync() makes the student's skills exactly this list
        $request->user()->studentProfile->skills()->sync(array_unique($ids));

        return back()->with('success', 'Skills saved.');
    }

    // ----- Experience -----
    public function addExperience(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:500',
        ]);

        $request->user()->studentProfile->experiences()->create($data);

        return back()->with('success', 'Experience added.');
    }

    public function deleteExperience(Request $request, $id)
    {
        // looking only inside THIS student's experiences = a student can delete only their own
        $request->user()->studentProfile->experiences()->where('id', $id)->delete();

        return back()->with('success', 'Experience removed.');
    }

    // ----- Weekly availability -----
    public function saveAvailability(Request $request)
    {
        $student = $request->user()->studentProfile;
        $days = config('aralynk.days');

        // check the times only for the ticked days
        $rules = [];
        $messages = [];
        foreach ($days as $number => $name) {
            if ($request->has('day_' . $number)) {
                $rules['start_' . $number] = 'required|date_format:H:i';
                $rules['end_' . $number] = 'required|date_format:H:i|after:start_' . $number;
                $messages['start_' . $number . '.required'] = 'Enter a start time for ' . $name . '.';
                $messages['start_' . $number . '.date_format'] = 'The start time for ' . $name . ' is not valid.';
                $messages['end_' . $number . '.required'] = 'Enter an end time for ' . $name . '.';
                $messages['end_' . $number . '.date_format'] = 'The end time for ' . $name . ' is not valid.';
                $messages['end_' . $number . '.after'] = 'The end time for ' . $name . ' must be after the start time.';
            }
        }
        $request->validate($rules, $messages);

        DB::transaction(function () use ($request, $student, $days) {
            $student->availabilities()->delete();                       // remove the old list
            foreach ($days as $number => $name) {
                if ($request->has('day_' . $number)) {                  // save the ticked days
                    $student->availabilities()->create([
                        'day_of_week' => $number,
                        'start_time' => $request->input('start_' . $number),
                        'end_time' => $request->input('end_' . $number),
                    ]);
                }
            }
        });

        return back()->with('success', 'Weekly availability saved.');
    }

    // ----- "Available for this day": one specific date -----
    public function addDay(Request $request)
    {
        $data = $request->validate([
            'avail_date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(180)->toDateString(),
            'day_start' => 'required|date_format:H:i',
            'day_end' => 'required|date_format:H:i|after:day_start',
        ], ['day_end.after' => 'The end time must be after the start time.']);

        // one row per date: replace it if that date was already added
        StudentDayAvailability::updateOrCreate(
            ['student_profile_id' => $request->user()->studentProfile->id, 'avail_date' => $data['avail_date']],
            ['start_time' => $data['day_start'], 'end_time' => $data['day_end']]
        );

        return back()->with('success', 'You are marked available on ' . date('M j, Y', strtotime($data['avail_date'])) . '.');
    }

    public function deleteDay(Request $request, $id)
    {
        $request->user()->studentProfile->dayAvailabilities()->where('id', $id)->delete();

        return back()->with('success', 'Day removed.');
    }
}
