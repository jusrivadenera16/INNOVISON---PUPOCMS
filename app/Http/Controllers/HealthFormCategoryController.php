<?php

namespace App\Http\Controllers;

use App\Models\HealthFormCategory;
use Illuminate\Http\Request;

class HealthFormCategoryController extends Controller
{
    public function index()
    {
        $categories = HealthFormCategory::query()
            ->withCount('submissions')
            ->orderBy('name')
            ->get();

        return view('admin.reports.manage-health-form-categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $name = trim((string) $request->name);
        $duplicateExists = HealthFormCategory::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($duplicateExists) {
            return back()->withInput()->withErrors(['name' => 'This Health Form category already exists.']);
        }

        HealthFormCategory::create(['name' => $name, 'is_active' => true]);

        return back()->with('success', 'Health Form category added.');
    }

    public function destroy($id)
    {
        $category = HealthFormCategory::findOrFail($id);

        if ($category->submissions()->exists()) {
            $category->is_active = false;
            $category->save();

            return back()->with('success', 'Category has linked forms, so it was archived instead.');
        }

        $category->delete();

        return back()->with('success', 'Health Form category removed.');
    }
}
