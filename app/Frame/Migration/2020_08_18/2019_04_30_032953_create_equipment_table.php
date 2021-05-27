<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->bigIncrements('eq_id');
            $table->bigInteger('eq_ss_id')->nullable();
            $table->foreign('eq_ss_id', 'tbl_eq_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('eq_number');
            $table->string('eq_internal_number');
            $table->char('eq_transport', 1)->default('N');
            $table->bigInteger('eq_eg_id')->nullable();
            $table->foreign('eq_eg_id', 'tbl_eq_eg_id_foreign')->references('eg_id')->on('equipment_group');
            $table->char('eq_active', 1)->default('Y');
            $table->bigInteger('eq_created_by');
            $table->dateTime('eq_created_on');
            $table->bigInteger('eq_updated_by')->nullable();
            $table->dateTime('eq_updated_on')->nullable();
            $table->bigInteger('eq_deleted_by')->nullable();
            $table->dateTime('eq_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => EquipmentSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment');
    }
}
