<?php

namespace App\Http\Controllers;

use App\Models\HealthProfile;
use App\Services\{HealthFileStorage, TemporaryStudentFormCategoryService};
use Illuminate\Http\Request;

/** Remove together with the temporary_student_category_change feature flag when no longer needed. */
class TemporaryStudentFormCategoryController extends Controller
{
    public function show(HealthProfile $healthProfile, TemporaryStudentFormCategoryService $service)
    {
        $submission = $service->pending($healthProfile);
        return response()->json(['submission_id' => $submission->id, 'category' => trim((string) $submission->category),
            'categories' => $service->options(), 'student_name' => $healthProfile->user?->name,
        ])->withHeaders(HealthFileStorage::NO_STORE_HEADERS);
    }

    public function update(Request $request, HealthProfile $healthProfile, TemporaryStudentFormCategoryService $service)
    {
        $input = $request->validate([
            'submission_id' => ['required', 'integer', 'min:1'],
            'expected_category' => ['present', 'nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
        ]);
        $input['expected_category'] = trim((string) $input['expected_category']);
        $submission = $service->change($healthProfile->id, $input, $request->user());
        if ($request->hasSession()) $request->session()->flash('success', 'Student Health Form category updated. The same form is still pending approval.');
        return response()->json(['message' => 'Student Health Form category updated. The same submission is still pending approval.',
            'submission_id' => $submission->id, 'category' => $submission->category,
        ])->withHeaders(HealthFileStorage::NO_STORE_HEADERS);
    }
}
