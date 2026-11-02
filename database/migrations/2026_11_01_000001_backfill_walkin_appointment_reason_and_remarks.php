<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments')
            || !Schema::hasTable('consultations')
            || !Schema::hasColumn('appointments', 'consultation_id')
            || !Schema::hasColumn('appointments', 'reason')
            || !Schema::hasColumn('appointments', 'remarks')
            || !Schema::hasColumn('consultations', 'reason_for_visit')
            || !Schema::hasColumn('consultations', 'comments')) {
            return;
        }

        DB::table('appointments')
            ->whereIn('type', ['walkin', 'walk-in', 'walk_in'])
            ->whereNotNull('consultation_id')
            ->select(['id', 'reason', 'remarks', 'consultation_id'])
            ->orderBy('id')
            ->chunkById(500, function ($appointments) {
                foreach ($appointments as $appointment) {
                    $consultation = DB::table('consultations')
                        ->select(['reason_for_visit', 'comments'])
                        ->where('id', $appointment->consultation_id)
                        ->first();

                    if (!$consultation) {
                        continue;
                    }

                    $reason = trim((string) ($consultation->reason_for_visit ?? ''));
                    $consultationRemarks = trim((string) ($consultation->comments ?? ''));
                    $remarks = trim((string) ($appointment->remarks ?? ''));

                    if ($consultationRemarks !== '' && !str_contains($remarks, $consultationRemarks)) {
                        $remarks = $remarks === ''
                            ? $consultationRemarks
                            : $remarks . PHP_EOL . PHP_EOL . $consultationRemarks;
                    }

                    DB::table('appointments')
                        ->where('id', $appointment->id)
                        ->update([
                            'reason' => $reason !== ''
                                ? $reason
                                : ($consultationRemarks !== '' ? null : ($appointment->reason ?: null)),
                            'remarks' => $remarks !== '' ? $remarks : null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Keep the separated appointment values intact on rollback.
    }
};
