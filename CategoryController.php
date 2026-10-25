<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\JobPost;
use App\Models\StudentExperience;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $jobCounts = JobPost::selectRaw('category_id, COUNT(*) as total')->groupBy('category_id')->pluck('total', 'category_id');

        return view('admin.categories', ['categories' => $categories, 'jobCounts' => $jobCounts]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80|unique:categories,name']);
        Category::create(['name' => $data['name'], 'is_active' => true]);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate(['name' => ['required', 'string', 'max:80', Rule::unique('categories', 'name')->ignore($category->id)]]);
        $category->update(['name' => $data['name']]);

        return back()->with('success', 'Category renamed.');
    }

    public function toggle($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return back()->with('success', 'Category updated.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $used = JobPost::where('category_id', $id)->count()
              + BusinessProfile::where('category_id', $id)->count()
              + StudentExperience::where('category_id', $id)->count();
        if ($used > 0) {
            return back()->with('warning', 'This category is in use. Deactivate it instead of deleting it.');
        }

        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
