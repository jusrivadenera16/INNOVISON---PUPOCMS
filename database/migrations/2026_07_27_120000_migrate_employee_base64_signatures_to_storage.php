<?php

use App\Models\EmployeeHealthProfile;
use App\Services\HealthFileStorage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        EmployeeHealthProfile::query()
            ->whereNotNull('staff_signature')
            ->where('staff_signature', 'like', 'data:image/%')
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    $signatureData = trim((string) $profile->staff_signature);
                    if (!preg_match('/^data:image\/(png|jpe?g);base64,(.+)$/s', $signatureData, $matches)) {
                        continue;
                    }

                    $decodedSignature = base64_decode($matches[2], true);
                    if ($decodedSignature === false || $decodedSignature === '') {
                        continue;
                    }

                    $extension = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
                    $path = 'health_profile_employees/signatures/signature_' . uniqid('', true) . '.' . $extension;
                    app(HealthFileStorage::class)->put($path, $decodedSignature);

                    $profile->uploaded_signature_path = $path;
                    $profile->staff_signature = null;
                    $profile->signature_type = 'drawn';
                    $profile->save();
                }
            });
    }

    public function down(): void
    {
        // Converted files remain in storage; the original Base64 payload is not restored.
    }
};
