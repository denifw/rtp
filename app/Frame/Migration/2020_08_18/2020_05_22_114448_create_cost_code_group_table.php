<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostCodeGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_code_group', function (Blueprint $table) {
            $table->bigIncrements('ccg_id');
            $table->bigInteger('ccg_ss_id')->unsigned();
            $table->foreign('ccg_ss_id', 'tbl_ccg_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('ccg_srv_id')->unsigned()->nullable();
            $table->foreign('ccg_srv_id', 'tbl_ccg_srv_id_foreign')->references('srv_id')->on('service');
            $table->string('ccg_code', 50);
            $table->string('ccg_name', 150);
            $table->char('ccg_type', 1)->default('S');
            $table->char('ccg_active', 1)->default('Y');
            $table->bigInteger('ccg_created_by');
            $table->dateTime('ccg_created_on');
            $table->bigInteger('ccg_updated_by')->nullable();
            $table->dateTime('ccg_updated_on')->nullable();
            $table->bigInteger('ccg_deleted_by')->nullable();
            $table->dateTime('ccg_deleted_on')->nullable();
            $table->unique(['ccg_ss_id', 'ccg_srv_id', 'ccg_code'], 'tbl_ccg_ss_srv_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_code_group');
    }
}
