<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\JobPost;
use App\Models\Notification;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Post, edit, close, complete and delete jobs. Every query starts from THIS business's jobs,
// so a business can never touch another business's job.
class JobController extends Controller
{
    private function business()
    {
        return auth()->user()->businessProfile;
    }

    // ----- My jobs -----
    public function index()
    {
        $jobs = $this->business()->jobPosts()
            ->with('category')
            ->withCount([
                'applications as applicant_count' => function ($q) {
                    $q->where('status', '<>', 'Withdrawn');
                },
                'applications as accepted_count' => function ($q) {
                    $q->whereIn('status', ['Accepted', 'Completed']);
                },
            ])
            ->orderByDesc('id')
            ->get();

        return view('business.jobs', ['jobs' => $jobs]);
    }

    // ----- The form (new job or edit) -----
    public function create()
    {
        $job = new JobPost([
            'job_type' => '1-Day', 'work_location' => $this->business()->address,
            'start_time' => '08:00', 'end_time' => '17:00', 'slots' => 1, 'status' => 'open',
        ]);
        return $this->formView($job);
    }

    public function edit($id)
    {
        $job = $this->business()->jobPosts()->with(['skills', 'courses'])->findOrFail($id);
        return $this->formView($job);
    }

    private function formView($job)
    {
        return view('business.job_form', [
            'job' => $job,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'allSkills' => Skill::where('is_approved', true)->orderBy('name')->get(),
            'allCourses' => Course::orderBy('name')->get(),
            'checkedSkills' => old('skills', $job->exists ? $job->skills->pluck('id')->toArray() : []),
            'checkedCourses' => old('courses', $job->exists ? $job->courses->pluck('id')->toArray() : []),
            'checkedDays' => old('days', $job->exists ? $job->dayNumbers() : []),
        ]);
    }

    // ----- Save -----
    public function store(Request $request, MatchService $matcher)
    {
        return $this->save($request, new JobPost(), $matcher);
    }

    public function update(Request $request, MatchService $matcher, $id)
    {
        $job = $this->business()->jobPosts()->findOrFail($id);
        return $this->save($request, $job, $matcher);
    }

    private function save(Request $request, JobPost $job, MatchService $matcher)
    {
        $isNew = !$job->exists;

        // 1. Check every field
        $rules = [
            'title' => 'required|string|min:3|max:150',
            'description' => 'required|string|min:10|max:5000',
            'category_id' => 'required|exists:categories,id',
            'job_type' => ['required', Rule::in(config('aralynk.job_types'))],
            'work_location' => 'required|string|min:3|max:255',
            'work_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slots' => 'required|integer|between:1,100',
            'required_experience' => 'nullable|string|max:255',
            'additional_requirements' => 'nullable|string|max:5000',
            'compensation' => 'nullable|string|max:100',
            'deadline' => $isNew ? 'required|date|after_or_equal:today' : 'required|date',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
            'courses' => 'nullable|array',
            'courses.*' => 'integer|exists:courses,id',
            'days' => 'nullable|array',
            'days.*' => 'integer|between:1,7',
            'other_skills' => 'nullable|string|max:300',
        ];
        $data = $request->validate($rules, ['end_time.after' => 'The end time must be after the start time.']);

        // 2. Checks that depend on the job type
        $problems = [];
        $type = $data['job_type'];
        $workDate = $data['work_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $days = $data['days'] ?? [];

        if ($type == '1-Day' && !$workDate) {
            $problems['work_date'] = 'A 1-Day job needs a work date.';
        }
        if ($type == 'Several-Day') {
            if (!$workDate || !$endDate) {
                $problems['end_date'] = 'Several-Day jobs need a start date and an end date.';
            } elseif ($endDate < $workDate || (strtotime($endDate) - strtotime($workDate)) > 13 * 86400) {
                $problems['end_date'] = 'The end date must be on or after the start date, within 14 days.';
            }
        }
        if (($type == 'Recurring' || $type == 'Part-Time') && count($days) == 0) {
            $problems['days'] = 'Choose which weekdays the work happens.';
        }
        if ($workDate && $data['deadline'] > $workDate) {
            $problems['deadline'] = 'The application deadline should be on or before the work date.';
        }
        if (count($problems) > 0) {
            return back()->withInput()->withErrors($problems);
        }

        // 3. Save the job and its skills, courses and days together
        $business = $this->business();
        DB::transaction(function () use ($job, $data, $business, $request, $type, $workDate, $endDate, $days) {
            $job->fill([
                'business_profile_id' => $business->id,
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'job_type' => $type,
                'work_location' => $data['work_location'],
                'work_date' => $workDate,
                'end_date' => ($type == 'Several-Day') ? $endDate : null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'slots' => $data['slots'],
                'required_experience' => $data['required_experience'] ?? null,
                'additional_requirements' => $data['additional_requirements'] ?? null,
                'compensation' => $data['compensation'] ?? null,
                'deadline' => $data['deadline'],
            ]);
            if (!$job->exists) {
                $job->status = 'open';
            } elseif ($job->status != 'completed' && in_array($request->input('status'), ['open', 'closed'])) {
                $job->status = $request->input('status');
            }
            $job->save();

            // skills = the ticked ones + the "other skills" typed (new skills are created)
            $skillIds = array_merge($data['skills'] ?? [], Skill::addFromText($data['other_skills'] ?? ''));
            $job->skills()->sync(array_unique($skillIds));
            $job->courses()->sync($data['courses'] ?? []);

            DB::table('job_post_days')->where('job_post_id', $job->id)->delete();
            if ($type == 'Recurring' || $type == 'Part-Time') {
                foreach (array_unique($days) as $dayNumber) {
                    DB::table('job_post_days')->insert(['job_post_id' => $job->id, 'day_of_week' => $dayNumber]);
                }
            }
        });

        // 4. New job: tell students who are a good match (70% or more)
        if ($isNew) {
            $job->load(['skills', 'courses']);
            $students = StudentProfile::with(['skills', 'availabilities', 'dayAvailabilities', 'experiences'])
                ->whereHas('user', function ($q) {
                    $q->where('status', 'active');
                })->limit(300)->get();
            foreach ($students as $student) {
                $match = $matcher->getMatch($student, $job);
                if ($match['total'] >= 70) {
                    Notification::send($student->user_id, 'recommended', 'Recommended job: ' . $job->title . ' (' . $match['total'] . '% match)', 'student/jobs/' . $job->id);
                }
            }
        }

        return redirect()->route('business.jobs')->with('success', 'Job saved.');
    }

    // ----- Close, reopen, complete, delete -----
    public function close($id)
    {
        $this->business()->jobPosts()->findOrFail($id)->update(['status' => 'closed']);
        return back()->with('success', 'Job closed.');
    }

    public function reopen($id)
    {
        $job = $this->business()->jobPosts()->findOrFail($id);
        if ($job->deadline < now()->toDateString()) {
            return back()->with('warning', 'Edit the job and set a new deadline before reopening it.');
        }
        $job->update(['status' => 'open']);
        return back()->with('success', 'Job reopened.');
    }

    // Complete = the work is done. Accepted students become "Completed", and both sides can now rate each other.
    public function complete($id)
    {
        $job = $this->business()->jobPosts()->findOrFail($id);
        $job->update(['status' => 'completed']);

        $accepted = $job->applications()->where('status', 'Accepted')->with('student')->get();
        foreach ($accepted as $application) {
            $application->update(['status' => 'Completed']);
            Notification::send($application->student->user_id, 'application',
                'Job completed: ' . $job->title . '. You can now rate the business.', 'student/applications');
        }

        return back()->with('success', 'Job marked as completed. Accepted students are now Completed, and you can rate them from the Applicants page.');
    }

    public function destroy($id)
    {
        $this->business()->jobPosts()->findOrFail($id)->delete();
        return back()->with('success', 'Job deleted.');
    }
}
