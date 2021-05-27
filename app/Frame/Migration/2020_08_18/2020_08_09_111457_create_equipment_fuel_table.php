<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipmentFuelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_fuel', function (Blueprint $table) {
            $table->bigIncrements('eqf_id');
            $table->bigInteger('eqf_ss_id')->unsigned();
            $table->foreign('eqf_ss_id', 'tbl_eqf_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('eqf_eq_id')->unsigned();
            $table->foreign('eqf_eq_id', 'tbl_eqf_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->date('eqf_date');
            $table->float('eqf_meter');
            $table->float('eqf_qty_fuel');
            $table->float('eqf_cost');
            $table->string('eqf_remark', 255)->nullable();
            $table->string('eqf_deleted_reason', 255)->nullable();
            $table->bigInteger('eqf_confirm_by')->nullable();
            $table->dateTime('eqf_confirm_on')->nullable();
            $table->bigInteger('eqf_created_by');
            $table->dateTime('eqf_created_on');
            $table->bigInteger('eqf_updated_by')->nullable();
            $table->dateTime('eqf_updated_on')->nullable();
            $table->bigInteger('eqf_deleted_by')->nullable();
            $table->dateTime('eqf_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_fuel');
    }
}
