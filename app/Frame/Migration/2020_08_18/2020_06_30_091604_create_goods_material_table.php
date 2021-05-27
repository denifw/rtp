<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_material', function (Blueprint $table) {
            $table->bigIncrements('gm_id');
            $table->bigInteger('gm_gd_id')->unsigned();
            $table->foreign('gm_gd_id', 'tbl_gm_gd_id_foreign')->references('gd_id')->on('goods');

            $table->bigInteger('gm_goods_id')->unsigned();
            $table->foreign('gm_goods_id', 'tbl_gm_goods_id_foreign')->references('gd_id')->on('goods');
            $table->float('gm_quantity');
            $table->bigInteger('gm_gdu_id')->unsigned();
            $table->foreign('gm_gdu_id', 'tbl_gm_gdu_id_foreign')->references('gdu_id')->on('goods_unit');

            $table->bigInteger('gm_created_by');
            $table->dateTime('gm_created_on');
            $table->bigInteger('gm_updated_by')->nullable();
            $table->dateTime('gm_updated_on')->nullable();
            $table->bigInteger('gm_deleted_by')->nullable();
            $table->dateTime('gm_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_material');
    }
}
