<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_pdf_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('status', 20)->default('queued');
            $table->longText('filters');
            $table->longText('context');
            foreach (['source_max_id', 'source_total', 'cursor_id', 'scanned', 'total', 'rendered', 'parts', 'manifest_offset'] as $column) {
                $table->unsignedBigInteger($column)->default(0);
            }
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
        Schema::create('health_pdf_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_pdf_jobs');
        Schema::dropIfExists('health_pdf_exports');
    }
};
