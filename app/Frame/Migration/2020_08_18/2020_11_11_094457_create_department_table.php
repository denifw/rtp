<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department', function (Blueprint $table) {
            $table->bigIncrements('dpt_id');
            $table->bigInteger('dpt_ss_id')->unsigned();
            $table->foreign('dpt_ss_id', 'tbl_dpt_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('dpt_name', 256);
            $table->char('dpt_active', 1)->default('Y');
            $table->bigInteger('dpt_created_by');
            $table->dateTime('dpt_created_on');
            $table->bigInteger('dpt_updated_by')->nullable();
            $table->dateTime('dpt_updated_on')->nullable();
            $table->bigInteger('dpt_deleted_by')->nullable();
            $table->dateTime('dpt_deleted_on')->nullable();
            $table->string('dpt_deleted_reason', 256)->nullable();
            $table->unique(['dpt_ss_id', 'dpt_name'], 'tbl_dpt_ss_id_name_unique');
            $table->uuid('dpt_uid');
            $table->unique('dpt_uid', 'tbl_dpt_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('department');
    }
}
