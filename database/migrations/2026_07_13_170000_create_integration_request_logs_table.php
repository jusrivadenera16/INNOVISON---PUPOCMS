<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('integration_request_logs')) {
            return;
        }

        Schema::create('integration_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_client_id')->nullable()->constrained('integration_clients')->nullOnDelete();
            $table->unsignedBigInteger('token_id')->nullable()->index();
            $table->string('system_key')->index();
            $table->string('system_name')->nullable();
            $table->string('auth_method')->nullable();
            $table->string('http_method', 12);
            $table->string('endpoint');
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->integer('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['system_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_request_logs');
    }
};
