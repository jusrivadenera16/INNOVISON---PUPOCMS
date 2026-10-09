<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consultation_evaluations') && !Schema::hasTable('eval_form')) {
            Schema::rename('consultation_evaluations', 'eval_form');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eval_form') && !Schema::hasTable('consultation_evaluations')) {
            Schema::rename('eval_form', 'consultation_evaluations');
        }
    }
};
