<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStaffPersonalHistoryAndRequirementFiles extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('health_profile_staffs')) {
            return;
        }

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            foreach (['cigarette_smoking_details', 'alcohol_drinking_details', 'traveled_abroad_details'] as $column) {
                if (Schema::hasColumn('health_profile_staffs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_staffs', 'has_disability')) {
                $table->boolean('has_disability')->default(false)->after('traveled_abroad');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'disability_type')) {
                $table->string('disability_type')->nullable()->after('has_disability');
            }
        });

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_staffs', 'student_photo')) {
                $table->string('student_photo')->nullable()->after('disability_type');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'health_declaration')) {
                $table->string('health_declaration')->nullable()->after('student_photo');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'medical_certificate')) {
                $table->string('medical_certificate')->nullable()->after('health_declaration');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'chest_xray_document')) {
                $table->string('chest_xray_document')->nullable()->after('medical_certificate');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'pwd_id_proof')) {
                $table->string('pwd_id_proof')->nullable()->after('chest_xray_document');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('health_profile_staffs')) {
            return;
        }

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            foreach (['has_disability', 'disability_type', 'student_photo', 'health_declaration', 'medical_certificate', 'chest_xray_document', 'pwd_id_proof'] as $column) {
                if (Schema::hasColumn('health_profile_staffs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_staffs', 'cigarette_smoking_details')) {
                $table->string('cigarette_smoking_details')->nullable()->after('cigarette_smoking');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'alcohol_drinking_details')) {
                $table->string('alcohol_drinking_details')->nullable()->after('alcohol_drinking');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'traveled_abroad_details')) {
                $table->string('traveled_abroad_details')->nullable()->after('traveled_abroad');
            }
        });
    }
}
