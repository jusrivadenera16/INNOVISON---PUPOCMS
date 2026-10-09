<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_evaluations', function (Blueprint $table) {
            $table->timestamp('consent_at')->nullable()->after('status');
            $table->string('client_type', 80)->nullable()->after('consent_at');
            $table->string('sex', 40)->nullable()->after('client_type');
            $table->string('age_group', 40)->nullable()->after('sex');
            $table->string('cc1', 10)->nullable()->after('age_group');
            $table->string('cc2', 10)->nullable()->after('cc1');
            $table->string('cc3', 10)->nullable()->after('cc2');
            $table->json('sqd_answers')->nullable()->after('cc3');
            $table->text('suggestions')->nullable()->after('feedback');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'consent_at',
                'client_type',
                'sex',
                'age_group',
                'cc1',
                'cc2',
                'cc3',
                'sqd_answers',
                'suggestions',
            ]);
        });
    }
};
