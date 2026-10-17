<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrivacyConsentMetadataToHealthProfiles extends Migration
{
    private $tables = [
        'health_profiles',
        'health_profile_emp',
        'dependents_profiles',
    ];

    public function up()
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'privacy_consent_version')) {
                    $table->string('privacy_consent_version', 100)->nullable();
                }

                if (!Schema::hasColumn($tableName, 'privacy_consent_at')) {
                    $table->timestamp('privacy_consent_at')->nullable();
                }
            });
        }
    }

    public function down()
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = [];
                if (Schema::hasColumn($tableName, 'privacy_consent_version')) {
                    $columns[] = 'privacy_consent_version';
                }
                if (Schema::hasColumn($tableName, 'privacy_consent_at')) {
                    $columns[] = 'privacy_consent_at';
                }
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
}
