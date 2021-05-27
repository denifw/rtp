<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead', function (Blueprint $table) {
            $table->bigIncrements('ld_id');
            $table->bigInteger('ld_ss_id')->unsigned();
            $table->foreign('ld_ss_id', 'tbl_ld_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('ld_number', 256);
            $table->bigInteger('ld_rel_id')->unsigned();
            $table->foreign('ld_rel_id', 'tbl_ld_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('ld_sty_id')->unsigned();
            $table->foreign('ld_sty_id', 'tbl_ld_sty_id_foreign')->references('sty_id')->on('system_type');
            $table->dateTime('ld_converted_on')->nullable();
            $table->bigInteger('ld_converted_by')->nullable();
            $table->foreign('ld_converted_by', 'tbl_ld_converted_by_foreign')->references('us_id')->on('users');
            $table->bigInteger('ld_created_by');
            $table->dateTime('ld_created_on');
            $table->bigInteger('ld_updated_by')->nullable();
            $table->dateTime('ld_updated_on')->nullable();
            $table->bigInteger('ld_deleted_by')->nullable();
            $table->dateTime('ld_deleted_on')->nullable();
            $table->string('ld_deleted_reason', 256)->nullable();
            $table->uuid('ld_uid');
            $table->unique('ld_uid', 'tbl_ld_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead');
    }
}
