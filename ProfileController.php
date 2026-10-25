<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return view('business.profile', [
            'business' => $user->businessProfile,
            'email' => $user->email,
            'categories' => Category::orderBy('name')->get(),
            'ratingAverage' => $user->averageRating(),
            'ratingCount' => $user->ratingsReceived()->count(),
            'ratings' => $user->ratingsReceived()->with(['rater.studentProfile', 'application.jobPost'])->orderByDesc('id')->limit(10)->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|min:2|max:150',
            'contact_person' => 'required|string|min:3|max:120',
            'contact_no' => ['required', 'regex:/^[0-9+\- ]{7,20}$/'],
            'address' => 'required|string|min:5|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:2000',
        ]);

        $request->user()->businessProfile->update($data);
        $request->user()->update(['name' => $data['business_name']]);

        return back()->with('success', 'Business profile updated.');
    }
}
