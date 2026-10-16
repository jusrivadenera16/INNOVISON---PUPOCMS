<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('consultation_drafts') || Schema::hasColumn('consultation_drafts', 'payload_encrypted')) {
            return;
        }

        Schema::table('consultation_drafts', function (Blueprint $table): void {
            $table->longText('payload_encrypted')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('consultation_drafts') || !Schema::hasColumn('consultation_drafts', 'payload_encrypted')) {
            return;
        }

        DB::table('consultation_drafts')
            ->whereNotNull('payload_encrypted')
            ->orderBy('id')
            ->chunkById(100, function ($drafts): void {
                foreach ($drafts as $draft) {
                    $payload = Crypt::decrypt($draft->payload_encrypted);
                    if (!is_array($payload)) {
                        throw new RuntimeException('A consultation draft payload could not be restored.');
                    }

                    DB::table('consultation_drafts')
                        ->where('id', $draft->id)
                        ->update(['payload' => json_encode($payload, JSON_THROW_ON_ERROR)]);
                }
            });

        Schema::table('consultation_drafts', function (Blueprint $table): void {
            $table->dropColumn('payload_encrypted');
        });
    }
};
