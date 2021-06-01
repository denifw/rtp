<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_mapping', function (Blueprint $table) {
            $table->uuid('ump_id')->primary();
            $table->uuid('ump_us_id')->nullable();
            $table->foreign('ump_us_id', 'tbl_ump_us_id_fkey')->references('us_id')->on('users');
            $table->uuid('ump_ss_id')->nullable();
            $table->foreign('ump_ss_id', 'tbl_ump_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->uuid('ump_rel_id')->nullable();
            $table->foreign('ump_rel_id', 'tbl_ump_rel_id_fkey')->references('rel_id')->on('relation');
            $table->uuid('ump_cp_id')->nullable();
            $table->foreign('ump_cp_id', 'tbl_ump_cp_id_fkey')->references('cp_id')->on('contact_person');
            $table->char('ump_confirm', 1)->default('N');
            $table->char('ump_default', 1)->default('N');
            $table->char('ump_active', 1)->default('Y');
            $table->uuid('ump_created_by');
            $table->dateTime('ump_created_on');
            $table->uuid('ump_updated_by')->nullable();
            $table->dateTime('ump_updated_on')->nullable();
            $table->uuid('ump_deleted_by')->nullable();
            $table->dateTime('ump_deleted_on')->nullable();
            $table->string('ump_deleted_reason', 256)->nullable();
            $table->unique(['ump_ss_id', 'ump_us_id'], 'tbl_ump_ss_id_us_id_unique');
            $table->unique(['ump_ss_id', 'ump_cp_id'], 'tbl_ump_ss_id_cp_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_mapping');
    }
}
