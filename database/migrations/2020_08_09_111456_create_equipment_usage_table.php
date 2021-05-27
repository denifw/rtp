<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipmentUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_usage', function (Blueprint $table) {
            $table->bigIncrements('equ_id');
            $table->bigInteger('equ_ss_id')->unsigned();
            $table->foreign('equ_ss_id', 'tbl_equ_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('equ_eq_id')->unsigned();
            $table->foreign('equ_eq_id', 'tbl_equ_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->date('equ_date');
            $table->float('equ_meter');
            $table->string('equ_remark', 255)->nullable();
            $table->bigInteger('equ_created_by');
            $table->dateTime('equ_created_on');
            $table->bigInteger('equ_updated_by')->nullable();
            $table->dateTime('equ_updated_on')->nullable();
            $table->bigInteger('equ_deleted_by')->nullable();
            $table->dateTime('equ_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_usage');
    }
}
