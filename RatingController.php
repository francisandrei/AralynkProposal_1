<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Rating;
use Illuminate\Http\Request;

// Ratings moderation: the admin can remove an abusive rating or comment.
class RatingController extends Controller
{
    public function index(Request $request)
    {
        $max = (int) $request->input('max');          // optional filter: show only ratings of this many stars or less

        $query = Rating::with(['rater', 'ratee', 'application.jobPost']);
        if ($max >= 1 && $max <= 5) {
            $query->where('stars', '<=', $max);
        }

        return view('admin.ratings', ['ratings' => $query->orderByDesc('id')->limit(200)->get(), 'max' => $max]);
    }

    public function destroy(Request $request, $id)
    {
        $rating = Rating::findOrFail($id);
        ActivityLog::create(['user_id' => $request->user()->id, 'action' => 'rating_delete', 'details' => 'rating ' . $rating->id . ' for user ' . $rating->ratee_id]);
        $rating->delete();

        return back()->with('success', 'Rating removed.');
    }
}
