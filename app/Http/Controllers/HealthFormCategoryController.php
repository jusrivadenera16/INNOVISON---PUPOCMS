<?php

namespace App\Http\Controllers;

use App\Models\HealthFormCategory;
use App\Models\MarClearanceSourceMapping;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HealthFormCategoryController extends Controller
{
    public function index()
    {
        $categories = HealthFormCategory::query()
            ->withCount(['submissions', 'employeeProfiles', 'marSourceMappings'])
            ->orderBy('name')
            ->get();

        return view('admin.reports.manage-health-form-categories', [
            'categories' => $categories,
            'audienceLabels' => HealthFormCategory::AUDIENCE_LABELS,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'audiences' => ['required', 'array', 'min:1'],
            'audiences.*' => ['string', Rule::in(array_keys(HealthFormCategory::AUDIENCE_LABELS))],
        ]);

        $name = trim((string) $request->name);
        $duplicateExists = HealthFormCategory::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($duplicateExists) {
            return back()->withInput()->withErrors(['name' => 'This Health Form category already exists.']);
        }

        HealthFormCategory::create([
            'name' => $name,
            'is_active' => true,
            'available_for' => array_values(array_unique($request->input('audiences', []))),
        ]);

        return back()->with('success', 'Health Form category added.');
    }

    public function update(Request $request, $id)
    {
        $category = HealthFormCategory::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'audiences' => ['required', 'array', 'min:1'],
            'audiences.*' => ['string', Rule::in(array_keys(HealthFormCategory::AUDIENCE_LABELS))],
        ]);

        $name = trim((string) $request->name);
        $duplicateExists = HealthFormCategory::query()
            ->where('id', '<>', $category->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($duplicateExists) {
            return back()->withInput()->withErrors(['name' => 'This Health Form category already exists.']);
        }

        $hasLinkedRecords = $category->submissions()->exists()
            || $category->employeeProfiles()->exists()
            || MarClearanceSourceMapping::query()
                ->where('source_category_id', $category->id)
                ->exists();

        if ($hasLinkedRecords && strcasecmp($category->name, $name) !== 0) {
            return back()->withInput()->withErrors([
                'name' => 'This category has linked records or MAR mappings, so its name cannot be changed. You can still update its audience settings.',
            ]);
        }

        $category->update([
            'name' => $name,
            'available_for' => array_values(array_unique($request->input('audiences', []))),
        ]);

        return back()->with('success', 'Health Form category updated.');
    }

    public function destroy($id)
    {
        $category = HealthFormCategory::findOrFail($id);

        $hasLinkedRecords = $category->submissions()->exists()
            || $category->employeeProfiles()->exists()
            || MarClearanceSourceMapping::query()
                ->where('source_category_id', $category->id)
                ->exists();

        if ($hasLinkedRecords) {
            $category->is_active = false;
            $category->save();

            return back()->with('success', 'Category has linked forms, employee records, or MAR mappings, so it was archived instead.');
        }

        $category->delete();

        return back()->with('success', 'Health Form category removed.');
    }
}
