<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipmentGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_group', function (Blueprint $table) {
            $table->bigIncrements('eg_id');
            $table->string('eg_name');
            $table->float('eg_length')->nullable();
            $table->float('eg_width')->nullable();
            $table->float('eg_height')->nullable();
            $table->float('eg_net_weight')->nullable();
            $table->float('eg_gross_weight')->nullable();
            $table->float('eg_volume')->nullable();
            $table->bigInteger('eg_tm_id')->nullable();
            $table->foreign('eg_tm_id', 'tbl_eg_tm_id_foreign')->references('tm_id')->on('transport_module');
            $table->char('eg_active', 1)->default('Y');
            $table->bigInteger('eg_created_by');
            $table->dateTime('eg_created_on');
            $table->bigInteger('eg_updated_by')->nullable();
            $table->dateTime('eg_updated_on')->nullable();
            $table->bigInteger('eg_deleted_by')->nullable();
            $table->dateTime('eg_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => EquipmentGroupSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_group');
    }
}
