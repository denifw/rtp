<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipmentMeterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_meter', function (Blueprint $table) {
            $table->bigIncrements('eqm_id');
            $table->bigInteger('eqm_eq_id')->unsigned();
            $table->foreign('eqm_eq_id', 'tbl_eqm_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->float('eqm_meter');
            $table->date('eqm_date');
            $table->string('eqm_source', 255);
            $table->bigInteger('eqm_created_by');
            $table->dateTime('eqm_created_on');
            $table->bigInteger('eqm_updated_by')->nullable();
            $table->dateTime('eqm_updated_on')->nullable();
            $table->bigInteger('eqm_deleted_by')->nullable();
            $table->dateTime('eqm_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_meter');
    }
}
