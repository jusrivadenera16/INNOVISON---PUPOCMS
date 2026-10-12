<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('eval_form')) {
            return;
        }

        $addSourceType = !Schema::hasColumn('eval_form', 'source_type');
        $addSourceId = !Schema::hasColumn('eval_form', 'source_id');

        if ($addSourceType || $addSourceId) {
            Schema::table('eval_form', function (Blueprint $table) use ($addSourceType, $addSourceId) {
                if ($addSourceType) {
                    $table->string('source_type', 40)->default('consultation')->after('consultation_id');
                }

                if ($addSourceId) {
                    $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
                }
            });
        }

        Schema::table('eval_form', function (Blueprint $table) {
            $table->unsignedBigInteger('consultation_id')->nullable()->change();
        });

        DB::table('eval_form')
            ->whereNotNull('consultation_id')
            ->update([
                'source_type' => 'consultation',
                'source_id' => DB::raw('consultation_id'),
            ]);

        $indexes = DB::select('SHOW INDEX FROM eval_form');
        $sourceIndexExists = false;

        foreach ($indexes as $index) {
            if ($index->Key_name === 'eval_form_source_unique') {
                $sourceIndexExists = true;
                break;
            }
        }

        if (!$sourceIndexExists) {
            Schema::table('eval_form', function (Blueprint $table) {
                $table->unique(['source_type', 'source_id'], 'eval_form_source_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('eval_form')) {
            return;
        }

        if (DB::table('eval_form')->whereNull('consultation_id')->exists()) {
            throw new RuntimeException('Cannot roll back approval evaluations while evaluation rows have no consultation.');
        }

        Schema::table('eval_form', function (Blueprint $table) {
            $table->dropUnique('eval_form_source_unique');
            $table->dropColumn(['source_type', 'source_id']);
            $table->unsignedBigInteger('consultation_id')->nullable(false)->change();
        });
    }
};
