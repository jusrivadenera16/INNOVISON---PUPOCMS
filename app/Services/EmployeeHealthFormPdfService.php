<?php

namespace App\Services;

use App\Models\EmployeeHealthProfile;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeHealthFormPdfService
{
    public function __construct(private HealthFileStorage $healthFiles)
    {
    }

    public function generate(EmployeeHealthProfile $profile): string
    {
        $profile->refresh();
        $profile->loadMissing('user');

        $identifier = trim((string) (
            $profile->employee_number
            ?: $profile->user?->employee_number
            ?: $profile->id
        ));
        $safeIdentifier = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $identifier)
            ?: 'employee-' . $profile->id;
        $path = 'health_profile_employees/health_forms/health-form-'
            . $safeIdentifier . '-' . now()->format('YmdHis') . '.pdf';

        $previousPath = $this->normalizeStoragePath((string) $profile->staff_health_form_pdf_path);

        $pdf = Pdf::loadView('student.print_employee_health_form', [
            'user' => $profile->user,
            'employeeProfile' => $profile,
            'adminViewer' => true,
            'pdfMode' => true,
        ])->setPaper([0, 0, 612, 936]);

        if (!$this->healthFiles->put($path, $pdf->output())) {
            throw new \RuntimeException('Unable to write the Employee Health Form PDF.');
        }

        try {
            $profile->staff_health_form_pdf_path = $path;
            $profile->save();
        } catch (\Throwable $exception) {
            if ($path !== $previousPath) {
                $this->healthFiles->delete($path);
            }

            throw $exception;
        }

        if (
            $previousPath !== ''
            && $previousPath !== $path
            && str_starts_with($previousPath, 'health_profile_employees/health_forms/')
            && $this->healthFiles->exists($previousPath)
        ) {
            $this->healthFiles->delete($previousPath);
        }

        return $path;
    }

    private function normalizeStoragePath(string $path): string
    {
        $path = ltrim($path, '/');

        return preg_replace('#^(?:public/)?storage/#', '', $path) ?? $path;
    }
}
