<?php

namespace Tests\Unit;

use App\Support\HealthFormRecoveryInput;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class HealthFormRecoveryInputTest extends TestCase
{
    public function test_it_keeps_form_values_but_excludes_files_and_signature_payloads(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'health-form-');
        file_put_contents($temporaryFile, 'test file');

        try {
            $request = Request::create('/health-form', 'POST', [
                '_token' => 'csrf-token',
                '_method' => 'PUT',
                'first_name' => 'Test User',
                'medical_conditions' => ['Asthma'],
                'digital_signature_data' => 'data:image/png;base64,private-signature',
                'guardian_signature_data' => 'data:image/png;base64,private-guardian-signature',
                'employee_signature' => 'data:image/png;base64,private-employee-signature',
            ], [], [
                'medical_certificate' => new UploadedFile(
                    $temporaryFile,
                    'medical-certificate.pdf',
                    'application/pdf',
                    UPLOAD_ERR_OK,
                    true
                ),
            ]);

            $recoveryInput = HealthFormRecoveryInput::fromRequest($request);

            $this->assertSame('Test User', $recoveryInput['first_name']);
            $this->assertSame(['Asthma'], $recoveryInput['medical_conditions']);
            $this->assertArrayNotHasKey('_token', $recoveryInput);
            $this->assertArrayNotHasKey('_method', $recoveryInput);
            $this->assertArrayNotHasKey('medical_certificate', $recoveryInput);
            $this->assertArrayNotHasKey('digital_signature_data', $recoveryInput);
            $this->assertArrayNotHasKey('guardian_signature_data', $recoveryInput);
            $this->assertArrayNotHasKey('employee_signature', $recoveryInput);
        } finally {
            @unlink($temporaryFile);
        }
    }
}
