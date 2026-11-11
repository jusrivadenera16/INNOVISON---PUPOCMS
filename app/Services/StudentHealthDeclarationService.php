<?php

namespace App\Services;

use App\Models\HealthProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class StudentHealthDeclarationService
{
    public function purpose(?string $category, ?string $studentType = null): array
    {
        $category = trim((string) $category);
        $key = strtolower($category);
        // A new form's explicit purpose takes precedence over the account's student type.
        if (in_array($key, ['', 'general', 'student', 'student health form'], true)) {
            $key = strtolower(trim((string) $studentType));
            $category = '';
        }
        return match (true) {
            str_contains($key, 'ojt'), str_contains($key, 'on-the-job') => ['purpose' => 'On-the-Job Training (OJT)', 'endorsement' => 'On-the-Job Training (OJT)'],
            str_contains($key, 'ladder') => ['purpose' => 'Ladderized Program', 'endorsement' => 'Ladderized Program'],
            str_contains($key, 'return to school'), $key === 'returnee' => ['purpose' => 'returning to school', 'endorsement' => 'return to school as an enrolled student'],
            $key === 'shiftee' => ['purpose' => 'shifting academic program', 'endorsement' => 'status as an enrolled student in a new academic program'],
            $key === 'transferee' => ['purpose' => 'currently enrolled transferee student', 'endorsement' => 'status as a currently enrolled transferee student'],
            $category !== '' => ['purpose' => $category, 'endorsement' => $category],
            default => ['purpose' => 'currently enrolled student', 'endorsement' => 'status as a currently enrolled student'],
        };
    }

    public function generate(HealthProfile $profile, User $user, string $category, CarbonInterface $submittedAt, ?string $remarks = null): string
    {
        $purpose = $this->purpose($category, $user->student_type);
        $images = app(StoredImageDataUri::class);
        $age = (int) $profile->age;
        $fullName = trim(implode(' ', array_filter([$user->first_name, $user->middle_name, $user->last_name, $user->suffix_name]))) ?: $user->name;
        $pdf = Pdf::loadView('student.print_declaration_form', [
            'user' => $user, 'studentFullName' => $fullName,
            'studentNumber' => $profile->student_number ?: $user->student_number ?: $profile->reference_number,
            'course' => $profile->course_college ?: $user->course, 'age' => $age,
            'guardianName' => $profile->guardian_name, 'isMinor' => $age < 18,
            'categorySelected' => $category, 'purposeUnderline1' => $purpose['purpose'], 'purposeUnderline2' => $purpose['endorsement'],
            'studentSignatureSrc' => $images->fromStorage($profile->digital_signature),
            'guardianSignatureSrc' => $age < 18 ? $images->fromStorage($profile->guardian_signature) : null,
            'signatureDate' => $submittedAt->format('m/d/Y'), 'remarks' => $remarks ?? '',
        ]);
        $pdf->setPaper('letter', 'portrait');
        $path = 'health_profiles/health_declarations/declaration-' . $user->id . '-' . Str::uuid() . '.pdf';
        if (!app(HealthFileStorage::class)->put($path, $pdf->output())) {
            throw new \RuntimeException('Unable to save the new Health Form declaration.');
        }
        return $path;
    }
}
