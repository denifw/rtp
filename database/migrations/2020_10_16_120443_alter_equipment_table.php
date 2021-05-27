<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('eq_br_id');
            $table->dropColumn('eq_fuel_type');
            $table->float('eq_fuel_consume')->nullable();
            $table->bigInteger('eq_sty_id')->unsigned()->nullable();
            $table->foreign('eq_sty_id','tbl_eq_sty_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('eq_fty_id')->unsigned()->nullable();
            $table->foreign('eq_fty_id','tbl_eq_fty_id_foreign')->references('sty_id')->on('system_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
