<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDependentsProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('dependents_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('idp_user_id')->nullable();
            $table->string('idp_role')->nullable();
            $table->string('id_number')->nullable();

            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix_name')->nullable();
            $table->string('email')->nullable();

            $table->date('birthday')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('civil_status')->nullable();

            $table->string('street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('home_address')->nullable();

            $table->string('contact_no')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_no')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('idp_user_id');
            $table->index('idp_role');
            $table->index('id_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dependents_profiles');
    }
}
