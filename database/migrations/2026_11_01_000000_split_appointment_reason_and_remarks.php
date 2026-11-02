<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        $addReason = !Schema::hasColumn('appointments', 'reason');
        $addRemarks = !Schema::hasColumn('appointments', 'remarks');
        Schema::table('appointments', function (Blueprint $table) use ($addReason, $addRemarks) {
            if ($addReason) {
                $table->text('reason')->nullable();
            }

            if ($addRemarks) {
                $table->text('remarks')->nullable();
            }
        });

        if (Schema::hasColumn('appointments', 'notes')) {
            $columns = ['id', 'notes', 'reason', 'remarks'];
            $hasType = Schema::hasColumn('appointments', 'type');
            $hasConsultationId = Schema::hasColumn('appointments', 'consultation_id');
            if ($hasType) {
                $columns[] = 'type';
            }
            if ($hasConsultationId) {
                $columns[] = 'consultation_id';
            }
            $hasProblem = Schema::hasColumn('appointments', 'problem');
            if ($hasProblem) {
                $columns[] = 'problem';
            }
            $hasConsultationReason = Schema::hasTable('consultations')
                && Schema::hasColumn('consultations', 'reason_for_visit')
                && Schema::hasColumn('consultations', 'comments');

            DB::table('appointments')
                ->select($columns)
                ->orderBy('id')
                ->chunkById(500, function ($appointments) use ($hasProblem, $hasType, $hasConsultationId, $hasConsultationReason) {
                    foreach ($appointments as $appointment) {
                        $reason = trim((string) ($appointment->reason ?? ''));
                        $remarks = trim((string) ($appointment->remarks ?? ''));
                        $legacyText = trim((string) ($appointment->notes ?? ''));
                        $legacyReason = $legacyText;
                        $closureRemark = '';

                        if ($legacyText !== '') {
                            $closureMarker = '[Clinic Closure]';
                            $closurePosition = strpos($legacyText, $closureMarker);

                            if ($closurePosition !== false) {
                                $legacyReason = trim(substr($legacyText, 0, $closurePosition));
                                $closureRemark = trim(substr($legacyText, $closurePosition));
                            }
                        }

                        $isWalkIn = $hasType && in_array(strtolower(trim((string) ($appointment->type ?? ''))), ['walkin', 'walk-in', 'walk_in'], true);
                        $consultation = $isWalkIn && $hasConsultationId && $hasConsultationReason && $appointment->consultation_id
                            ? DB::table('consultations')->select(['reason_for_visit', 'comments'])->where('id', $appointment->consultation_id)->first()
                            : null;

                        if ($consultation) {
                            $consultationReason = trim((string) ($consultation->reason_for_visit ?? ''));
                            $consultationRemarks = trim((string) ($consultation->comments ?? ''));
                            if ($consultationReason !== '') {
                                $reason = $consultationReason;
                            } elseif ($consultationRemarks !== '') {
                                $reason = '';
                            } elseif ($reason === '') {
                                $reason = $legacyReason;
                            }

                            if ($consultationRemarks !== '' && !str_contains($remarks, $consultationRemarks)) {
                                $remarks = $remarks === ''
                                    ? $consultationRemarks
                                    : $remarks . PHP_EOL . PHP_EOL . $consultationRemarks;
                            }
                        } elseif ($reason === '') {
                            $reason = $legacyReason;
                        }

                        if ($closureRemark !== '' && !str_contains($remarks, $closureRemark)) {
                            $remarks = $remarks === ''
                                ? $closureRemark
                                : $remarks . PHP_EOL . PHP_EOL . $closureRemark;
                        }

                        if ($reason === '' && $hasProblem) {
                            $reason = trim((string) ($appointment->problem ?? ''));
                        }

                        DB::table('appointments')
                            ->where('id', $appointment->id)
                            ->update([
                                'reason' => $reason !== '' ? $reason : null,
                                'remarks' => $remarks !== '' ? $remarks : null,
                            ]);
                    }
                });

            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        if (!Schema::hasColumn('appointments', 'notes')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->text('notes')->nullable();
            });
        }

        $hasReason = Schema::hasColumn('appointments', 'reason');
        $hasRemarks = Schema::hasColumn('appointments', 'remarks');
        $columns = ['id', 'notes'];
        if ($hasReason) {
            $columns[] = 'reason';
        }
        if ($hasRemarks) {
            $columns[] = 'remarks';
        }

        DB::table('appointments')
            ->select($columns)
            ->orderBy('id')
            ->chunkById(500, function ($appointments) use ($hasReason, $hasRemarks) {
                foreach ($appointments as $appointment) {
                    $reason = $hasReason ? trim((string) ($appointment->reason ?? '')) : '';
                    $remarks = $hasRemarks ? trim((string) ($appointment->remarks ?? '')) : '';
                    $legacyText = trim(implode(PHP_EOL . PHP_EOL, array_filter([
                        $reason,
                        $remarks !== '' ? '[Clinic Remarks] ' . $remarks : '',
                    ])));

                    DB::table('appointments')
                        ->where('id', $appointment->id)
                        ->update(['notes' => $legacyText !== '' ? $legacyText : null]);
                }
            });

        Schema::table('appointments', function (Blueprint $table) use ($hasReason, $hasRemarks) {
            $columnsToDrop = array_values(array_filter([
                $hasReason ? 'reason' : null,
                $hasRemarks ? 'remarks' : null,
            ]));
            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
