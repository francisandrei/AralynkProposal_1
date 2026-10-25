<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Notification;
use App\Models\Rating;
use Illuminate\Http\Request;

// Two-way ratings. Used by BOTH sides:
//   a student rates the business, and the business rates the student.
// Rules: only the two people of that application, only after the status is Completed, once each.
class RatingController extends Controller
{
    public function store(Request $request, $id)
    {
        $application = Application::with(['student', 'jobPost.businessProfile'])->findOrFail($id);
        $user = $request->user();

        $studentUserId = $application->student->user_id;
        $businessUserId = $application->jobPost->businessProfile->user_id;

        // Who is rating whom?
        if ($user->id == $studentUserId) {
            $raterRole = 'student';
            $rateeId = $businessUserId;
            $link = 'student/applications';
        } elseif ($user->id == $businessUserId) {
            $raterRole = 'business';
            $rateeId = $studentUserId;
            $link = 'business/applicants';
        } else {
            abort(403);                                    // not part of this application
        }

        if ($application->status != 'Completed') {
            return back()->with('warning', 'You can rate only after the job is marked Completed.');
        }

        $data = $request->validate([
            'stars' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        $already = Rating::where('application_id', $application->id)->where('rater_id', $user->id)->exists();
        if ($already) {
            return back()->with('warning', 'You already rated this job.');
        }

        Rating::create([
            'application_id' => $application->id,
            'rater_id' => $user->id,
            'ratee_id' => $rateeId,
            'rater_role' => $raterRole,
            'stars' => $data['stars'],
            'comment' => $data['comment'] ?? null,
        ]);

        Notification::send($rateeId, 'rating', 'You received a ' . $data['stars'] . '-star rating for ' . $application->jobPost->title, $link);

        return back()->with('success', 'Thank you! Your rating was saved.');
    }
}
