<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseStorageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_storage', function (Blueprint $table) {
            $table->bigIncrements('whs_id');
            $table->bigInteger('whs_wh_id')->nullable();
            $table->foreign('whs_wh_id', 'tbl_whs_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->string('whs_name', 125);
            $table->float('whs_length')->nullable();
            $table->float('whs_height')->nullable();
            $table->float('whs_width')->nullable();
            $table->float('whs_volume')->nullable();
            $table->char('whs_active', 1)->default('Y');
            $table->bigInteger('whs_created_by');
            $table->dateTime('whs_created_on');
            $table->bigInteger('whs_updated_by')->nullable();
            $table->dateTime('whs_updated_on')->nullable();
            $table->bigInteger('whs_deleted_by')->nullable();
            $table->dateTime('whs_deleted_on')->nullable();
            $table->unique(['whs_name', 'whs_wh_id'], 'tbl_whs_wh_id_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_storage');
    }
}
