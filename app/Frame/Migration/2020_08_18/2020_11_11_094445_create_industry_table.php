<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndustryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('industry', function (Blueprint $table) {
            $table->bigIncrements('ids_id');
            $table->bigInteger('ids_ss_id')->unsigned();
            $table->foreign('ids_ss_id', 'tbl_ids_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('ids_name', 256);
            $table->char('ids_active', 1)->default('Y');
            $table->bigInteger('ids_created_by');
            $table->dateTime('ids_created_on');
            $table->bigInteger('ids_updated_by')->nullable();
            $table->dateTime('ids_updated_on')->nullable();
            $table->bigInteger('ids_deleted_by')->nullable();
            $table->dateTime('ids_deleted_on')->nullable();
            $table->string('ids_deleted_reason', 256)->nullable();
            $table->unique(['ids_ss_id', 'ids_name'], 'tbl_ids_ss_id_name_unique');
            $table->uuid('ids_uid');
            $table->unique('ids_uid', 'tbl_ids_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('industry');
    }
}
