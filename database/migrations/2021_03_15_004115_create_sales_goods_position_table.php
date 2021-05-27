<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesGoodsPositionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_goods_position', function (Blueprint $table) {
            $table->bigIncrements('sgp_id');
            $table->bigInteger('sgp_sog_id')->unsigned();
            $table->foreign('sgp_sog_id', 'tbl_sgp_sog_id_foreign')->references('sog_id')->on('sales_order_goods');
            $table->bigInteger('sgp_jo_id')->unsigned();
            $table->foreign('sgp_jo_id', 'tbl_sgp_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->dateTime('sgp_complete_on')->nullable();
            $table->bigInteger('sgp_created_by');
            $table->dateTime('sgp_created_on');
            $table->bigInteger('sgp_updated_by')->nullable();
            $table->dateTime('sgp_updated_on')->nullable();
            $table->bigInteger('sgp_deleted_by')->nullable();
            $table->dateTime('sgp_deleted_on')->nullable();
            $table->string('sgp_deleted_reason', 256)->nullable();
            $table->uuid('sgp_uid');
            $table->unique('sgp_uid', 'tbl_sgp_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_goods_position');
    }
}
