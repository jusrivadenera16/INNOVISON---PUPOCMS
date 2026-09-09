<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mar_clearance_issuances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('clearance_subcategory_id');
            $table->string('source_workflow', 40);
            $table->string('source_record_id', 120);
            $table->string('user_type', 60)->nullable();
            $table->string('user_name_snapshot', 180)->nullable();
            $table->string('clearance_name_snapshot', 160);
            $table->dateTime('approved_at');
            $table->timestamps();

            $table->foreign('user_id', 'mci_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('clearance_subcategory_id', 'mci_subcat_fk')
                ->references('id')
                ->on('mar_clearance_subcategories')
                ->restrictOnDelete();

            $table->unique(['source_workflow', 'source_record_id'], 'mci_source_record_unique');
            $table->index(['approved_at', 'user_type'], 'mci_approved_type_idx');
            $table->index(['clearance_subcategory_id', 'approved_at'], 'mci_subcat_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mar_clearance_issuances');
    }
};
