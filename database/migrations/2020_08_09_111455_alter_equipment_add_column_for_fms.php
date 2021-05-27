<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEquipmentAddColumnForFms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->bigInteger('eq_owt_id')->unsigned()->nullable();
            $table->foreign('eq_owt_id', 'tbl_eq_owt_id_foreign')->references('owt_id')->on('ownership_type');
            $table->bigInteger('eq_manage_by_id')->unsigned()->nullable();
            $table->foreign('eq_manage_by_id', 'tbl_eq_manage_by_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('eq_manager_id')->unsigned()->nullable();
            $table->foreign('eq_manager_id', 'tbl_eq_manager_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('eq_br_id')->unsigned()->nullable();
            $table->foreign('eq_br_id', 'tbl_eq_br_id_foreign')->references('br_id')->on('brand');
            $table->bigInteger('eq_built_year')->nullable();
            $table->string('eq_color', 255)->nullable();
            $table->bigInteger('eq_engine_capacity')->nullable();
            $table->string('eq_fuel_type', 255)->nullable();
            $table->bigInteger('eq_max_speed')->nullable();
            $table->string('eq_license_plate', 255)->nullable();
            $table->string('eq_machine_number', 255)->nullable();
            $table->string('eq_chassis_number', 255)->nullable();
            $table->string('eq_bpkb', 255)->nullable();
            $table->string('eq_stnk', 255)->nullable();
            $table->string('eq_keur', 255)->nullable();
            $table->string('eq_picture', 255)->nullable();
            $table->string('eq_primary_meter', 255)->nullable();
            $table->bigInteger('eq_eqs_id')->unsigned()->nullable()->default(1);
            $table->foreign('eq_eqs_id', 'tbl_eq_eqs_id_foreign')->references('eqs_id')->on('equipment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign('tbl_eq_owt_id_foreign');
            $table->dropColumn('eq_owt_id');
            $table->dropForeign('tbl_eq_manage_by_id_foreign');
            $table->dropColumn('eq_manage_by_id');
            $table->dropForeign('tbl_eq_manager_id_foreign');
            $table->dropColumn('eq_manager_id');
            $table->dropForeign('tbl_eq_br_id_foreign');
            $table->dropColumn('eq_br_id');
            $table->dropColumn('eq_built_year');
            $table->dropColumn('eq_color');
            $table->dropColumn('eq_engine_capacity');
            $table->dropColumn('eq_fuel_type');
            $table->dropColumn('eq_max_speed');
            $table->dropColumn('eq_license_plate');
            $table->dropColumn('eq_machine_number');
            $table->dropColumn('eq_chassis_number');
            $table->dropColumn('eq_bpkb');
            $table->dropColumn('eq_stnk');
            $table->dropColumn('eq_keur');
            $table->dropColumn('eq_picture');
            $table->dropColumn('eq_primary_meter');
            $table->dropForeign('tbl_eq_eqs_id_foreign');
            $table->dropColumn('eq_eqs_id');
        });
    }
}
