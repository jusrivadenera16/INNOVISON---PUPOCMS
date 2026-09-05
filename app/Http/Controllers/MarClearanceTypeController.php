<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\MarClearanceSubcategory;
use App\Models\MarClearanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarClearanceTypeController extends Controller
{
    public function index()
    {
        $clearanceTypes = MarClearanceType::query()
            ->where('is_active', true)
            ->with('subcategories')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.reports.manage-clearance-types', compact('clearanceTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        $name = trim($validated['name']);
        $exists = MarClearanceType::query()->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This clearance type already exists.']);
        }

        $baseCode = Str::snake(Str::limit($name, 45, '')) ?: 'clearance_type';
        $code = $baseCode;
        $suffix = 2;
        while (MarClearanceType::query()->where('code', $code)->exists()) {
            $code = $baseCode . '_' . $suffix++;
        }

        MarClearanceType::create([
            'code' => $code,
            'name' => $name,
            'sort_order' => (int) MarClearanceType::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Clearance type added.');
    }

    public function update(Request $request, MarClearanceType $marClearanceType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $name = trim($validated['name']);
        $exists = MarClearanceType::query()
            ->where('id', '!=', $marClearanceType->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This clearance type already exists.']);
        }

        $marClearanceType->update([
            'name' => $name,
            'sort_order' => $validated['sort_order'] ?? $marClearanceType->sort_order,
        ]);

        return back()->with('success', 'Clearance type updated.');
    }

    public function destroy(MarClearanceType $marClearanceType)
    {
        $linkedCodes = $marClearanceType->subcategories()->pluck('code')->push($marClearanceType->code);
        if ($marClearanceType->code === 'ojt') {
            $linkedCodes->push('coc_ijt');
        }

        if (Consultation::query()->whereIn('certificate_type', $linkedCodes)->exists()) {
            $marClearanceType->update(['is_active' => false]);
            return back()->with('success', 'Clearance type archived because it has linked consultations.');
        }

        $marClearanceType->delete();
        return back()->with('success', 'Clearance type removed.');
    }

    public function storeSubcategory(Request $request, MarClearanceType $marClearanceType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        $name = trim($validated['name']);
        $exists = $marClearanceType->subcategories()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This subcategory already exists under the selected clearance type.']);
        }

        $baseCode = Str::snake(Str::limit($marClearanceType->code . '_' . $name, 90, '')) ?: 'clearance_subcategory';
        $code = $baseCode;
        $suffix = 2;
        while (MarClearanceSubcategory::query()->where('code', $code)->exists()) {
            $code = $baseCode . '_' . $suffix++;
        }

        $marClearanceType->subcategories()->create([
            'code' => $code,
            'name' => $name,
            'sort_order' => (int) $marClearanceType->subcategories()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Clearance subcategory added.');
    }

    public function updateSubcategory(Request $request, MarClearanceSubcategory $marClearanceSubcategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        $name = trim($validated['name']);
        $exists = MarClearanceSubcategory::query()
            ->where('mar_clearance_type_id', $marClearanceSubcategory->mar_clearance_type_id)
            ->where('id', '!=', $marClearanceSubcategory->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This subcategory already exists under the selected clearance type.']);
        }

        $marClearanceSubcategory->update(['name' => $name]);

        return back()->with('success', 'Clearance subcategory updated.');
    }

    public function destroySubcategory(MarClearanceSubcategory $marClearanceSubcategory)
    {
        if (Consultation::query()->where('certificate_type', $marClearanceSubcategory->code)->exists()) {
            return back()->with('error', 'This subcategory cannot be removed because it has linked consultations.');
        }

        $marClearanceSubcategory->delete();

        return back()->with('success', 'Clearance subcategory removed.');
    }
}
