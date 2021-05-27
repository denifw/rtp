<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobStockTransferGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_stock_transfer_goods', function (Blueprint $table) {
            $table->bigIncrements('jtg_id');
            $table->bigInteger('jtg_jtr_id')->unsigned();
            $table->foreign('jtg_jtr_id', 'tbl_jtg_jtr_id_foreign')->references('jtr_id')->on('job_stock_transfer');
            $table->bigInteger('jtg_gd_id')->unsigned();
            $table->foreign('jtg_gd_id', 'tbl_jtg_gd_id_foreign')->references('gd_id')->on('goods');
            $table->bigInteger('jtg_gdu_id')->unsigned();
            $table->foreign('jtg_gdu_id', 'tbl_jtg_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
            $table->float('jtg_quantity');
            $table->string('jtg_production_number', 255)->nullable();
            $table->bigInteger('jtg_created_by');
            $table->dateTime('jtg_created_on');
            $table->bigInteger('jtg_updated_by')->nullable();
            $table->dateTime('jtg_updated_on')->nullable();
            $table->bigInteger('jtg_deleted_by')->nullable();
            $table->dateTime('jtg_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_stock_transfer_goods');
    }
}
