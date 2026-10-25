<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Notification;
use App\Models\Rating;
use App\Services\MatchService;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    // Applications that belong to THIS business's jobs only
    private function applications()
    {
        $businessId = auth()->user()->businessProfile->id;
        return Application::whereHas('jobPost', function ($q) use ($businessId) {
            $q->where('business_profile_id', $businessId);
        });
    }

    // ----- List of applicants -----
    public function index(Request $request, MatchService $matcher)
    {
        $user = $request->user();
        $business = $user->businessProfile;

        $jobFilter = (int) $request->input('job');
        $statusFilter = $request->input('status', '');
        $sort = $request->input('sort', 'date');     // the default order is DATE APPLIED on purpose (fairness)

        $query = $this->applications()->with([
            'student.course', 'student.user', 'student.skills', 'student.availabilities', 'student.dayAvailabilities', 'student.experiences',
            'jobPost.skills', 'jobPost.courses',
        ]);
        if ($jobFilter > 0) {
            $query->where('job_post_id', $jobFilter);
        }
        if (in_array($statusFilter, ['Pending', 'Reviewed', 'Accepted', 'Rejected', 'Withdrawn', 'Completed'])) {
            $query->where('status', $statusFilter);
        }
        $applications = $query->orderBy('created_at')->orderBy('id')->get();

        $list = [];
        foreach ($applications as $application) {
            $list[] = ['application' => $application, 'match' => $matcher->getMatch($application->student, $application->jobPost)];
        }
        if ($sort == 'match') {
            usort($list, function ($a, $b) {
                return $b['match']['total'] - $a['match']['total'];
            });
        }

        // average ratings of these students, and the ratings I already gave
        $studentUserIds = $applications->pluck('student.user_id')->unique()->toArray();
        $averages = Rating::whereIn('ratee_id', $studentUserIds)
            ->selectRaw('ratee_id, AVG(stars) as average, COUNT(*) as total')->groupBy('ratee_id')->get()->keyBy('ratee_id');
        $myRatings = Rating::where('rater_id', $user->id)->get()->keyBy('application_id');

        return view('business.applicants', [
            'list' => $list,
            'averages' => $averages,
            'myRatings' => $myRatings,
            'myJobs' => $business->jobPosts()->orderByDesc('id')->get(),
            'filters' => compact('jobFilter', 'statusFilter', 'sort'),
        ]);
    }

    // ----- One applicant's full profile -----
    public function show(Request $request, MatchService $matcher, $id)
    {
        $application = $this->applications()->with([
            'jobPost', 'student.user', 'student.course', 'student.skills', 'student.experiences.category',
            'student.availabilities', 'student.dayAvailabilities',
        ])->findOrFail($id);

        $student = $application->student;
        $studentUser = $student->user;

        return view('business.applicant_profile', [
            'application' => $application,
            'student' => $student,
            'studentUser' => $studentUser,
            'match' => $matcher->getMatch($student, $application->jobPost),
            'weekly' => $student->availabilities->keyBy('day_of_week'),
            'specificDays' => $student->dayAvailabilities->where('avail_date', '>=', now()->toDateString())->sortBy('avail_date'),
            // privacy: contact details are shown only after the business accepts the student
            'showContact' => in_array($application->status, ['Accepted', 'Completed']),
            'ratingAverage' => $studentUser->averageRating(),
            'ratingCount' => $studentUser->ratingsReceived()->count(),
            'ratings' => $studentUser->ratingsReceived()->with(['rater.businessProfile', 'application.jobPost'])->orderByDesc('id')->limit(5)->get(),
            'myRating' => Rating::where('application_id', $application->id)->where('rater_id', $request->user()->id)->first(),
        ]);
    }

    // ----- Review, accept, reject or complete -----
    public function updateStatus(Request $request, $id)
    {
        $application = $this->applications()->with(['jobPost', 'student'])->findOrFail($id);   // must be MY job
        $data = $request->validate(['new_status' => 'required|string']);

        if (!in_array($data['new_status'], $application->nextStatuses())) {
            return back()->with('warning', 'That status change is not allowed.');
        }

        $application->update(['status' => $data['new_status']]);

        $texts = ['Reviewed' => 'is being reviewed', 'Accepted' => 'was accepted', 'Rejected' => 'was not selected this time', 'Completed' => 'is marked completed'];
        $message = 'Your application for ' . $application->jobPost->title . ' ' . $texts[$data['new_status']];
        if ($data['new_status'] == 'Completed') {
            $message .= '. You can now rate the business.';
        }
        Notification::send($application->student->user_id, 'application', $message, 'student/applications');
        ActivityLog::create(['user_id' => $request->user()->id, 'action' => 'application_' . strtolower($data['new_status']), 'details' => 'application ' . $application->id]);

        return back()->with('success', 'Application marked ' . $data['new_status'] . '.');
    }
}
