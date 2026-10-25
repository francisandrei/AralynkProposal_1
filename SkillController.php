<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

// Students and businesses can type their own skills. The admin can add skills and delete wrong ones here.
class SkillController extends Controller
{
    public function index()
    {
        return view('admin.skills', ['skills' => Skill::withCount(['students', 'jobPosts'])->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:60|unique:skills,name']);
        Skill::create(['name' => $data['name'], 'is_approved' => true]);

        return back()->with('success', 'Skill added.');
    }

    public function destroy($id)
    {
        Skill::findOrFail($id)->delete();
        return back()->with('success', 'Skill deleted. It was removed from students and jobs.');
    }
}
