<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mar_clearance_issuances', 'clearance_type_id')) {
            Schema::table('mar_clearance_issuances', function (Blueprint $table) {
                $table->unsignedBigInteger('clearance_type_id')->nullable()->after('user_id');
                $table->foreign('clearance_type_id', 'mci_type_fk')
                    ->references('id')
                    ->on('mar_clearance_types')
                    ->restrictOnDelete();
                $table->index(['clearance_type_id', 'approved_at'], 'mci_type_date_idx');
            });
        }

        if (Schema::hasColumn('mar_clearance_issuances', 'clearance_subcategory_id')) {
            DB::statement(
                'ALTER TABLE `mar_clearance_issuances`
                 MODIFY `clearance_subcategory_id` BIGINT UNSIGNED NULL'
            );
        }

        DB::statement(
            'UPDATE `mar_clearance_issuances` AS i
             INNER JOIN `mar_clearance_subcategories` AS s
                 ON s.`id` = i.`clearance_subcategory_id`
             SET i.`clearance_type_id` = s.`mar_clearance_type_id`
             WHERE i.`clearance_type_id` IS NULL'
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('mar_clearance_issuances', 'clearance_subcategory_id')) {
            DB::statement(
                'ALTER TABLE `mar_clearance_issuances`
                 MODIFY `clearance_subcategory_id` BIGINT UNSIGNED NOT NULL'
            );
        }

        if (Schema::hasColumn('mar_clearance_issuances', 'clearance_type_id')) {
            Schema::table('mar_clearance_issuances', function (Blueprint $table) {
                $table->dropForeign('mci_type_fk');
                $table->dropIndex('mci_type_date_idx');
                $table->dropColumn('clearance_type_id');
            });
        }
    }
};
