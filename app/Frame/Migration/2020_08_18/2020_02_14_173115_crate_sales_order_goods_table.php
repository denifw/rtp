<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrateSalesOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_goods', function (Blueprint $table) {
            $table->bigIncrements('sog_id');
            $table->bigInteger('sog_so_id')->unsigned();
            $table->foreign('sog_so_id', 'tbl_sog_so_id_foreign')->references('so_id')->on('sales_order');
            $table->bigInteger('sog_gd_id')->unsigned()->nullable();
            $table->foreign('sog_gd_id', 'tbl_sog_gd_id_foreign')->references('gd_id')->on('goods');
            $table->float('sog_quantity')->nullable();
            $table->bigInteger('sog_gdu_id')->unsigned()->nullable();
            $table->foreign('sog_gdu_id', 'tbl_sog_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
            $table->string('sog_notes', 255)->nullable();
            $table->bigInteger('sog_created_by');
            $table->dateTime('sog_created_on');
            $table->bigInteger('sog_updated_by')->nullable();
            $table->dateTime('sog_updated_on')->nullable();
            $table->bigInteger('sog_deleted_by')->nullable();
            $table->dateTime('sog_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_goods');
    }
}
