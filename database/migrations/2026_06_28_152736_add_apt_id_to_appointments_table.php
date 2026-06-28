<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'apt_id')) {
                $table->string('apt_id')->nullable()->unique()->after('id');
            }
        });

        if (!Schema::hasColumn('appointments', 'apt_id')) {
            return;
        }

        $sequenceByMinute = [];

        DB::table('appointments')
            ->select('id', 'date', 'time', 'created_at')
            ->whereNull('apt_id')
            ->orderBy('date')
            ->orderBy('time')
            ->orderBy('id')
            ->chunkById(100, function ($appointments) use (&$sequenceByMinute) {
                foreach ($appointments as $appointment) {
                    try {
                        $baseDate = trim((string) $appointment->date) !== ''
                            ? trim((string) $appointment->date)
                            : Carbon::parse($appointment->created_at)->toDateString();
                        $baseTime = trim((string) $appointment->time) !== ''
                            ? trim((string) $appointment->time)
                            : Carbon::parse($appointment->created_at)->format('H:i:s');

                        $scheduledAt = Carbon::parse($baseDate . ' ' . $baseTime);
                    } catch (Throwable $exception) {
                        $scheduledAt = Carbon::parse($appointment->created_at ?: now());
                    }

                    $prefix = 'APT-' . $scheduledAt->format('dmy-Hi');
                    $sequenceByMinute[$prefix] = ($sequenceByMinute[$prefix] ?? 0) + 1;

                    DB::table('appointments')
                        ->where('id', $appointment->id)
                        ->update([
                            'apt_id' => $prefix . str_pad((string) $sequenceByMinute[$prefix], 2, '0', STR_PAD_LEFT),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'apt_id')) {
                $table->dropUnique(['apt_id']);
                $table->dropColumn('apt_id');
            }
        });
    }
};
