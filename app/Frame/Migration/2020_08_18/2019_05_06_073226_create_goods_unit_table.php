<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_unit', function (Blueprint $table) {
            $table->bigIncrements('gdu_id');
            $table->bigInteger('gdu_gd_id')->unsigned();
            $table->foreign('gdu_gd_id', 'tbl_gdu_gd_id_foreign')->references('gd_id')->on('goods');
            $table->float('gdu_quantity_based');
            $table->bigInteger('gdu_uom_based_id')->unsigned();
            $table->foreign('gdu_uom_based_id', 'tbl_gdu_uom_based_id_foreign')->references('uom_id')->on('unit');
            $table->float('gdu_quantity');
            $table->bigInteger('gdu_uom_id')->unsigned();
            $table->foreign('gdu_uom_id', 'tbl_gdu_uom_id_foreign')->references('uom_id')->on('unit');
            $table->char('gdu_active', 1)->default('Y');
            $table->bigInteger('gdu_created_by');
            $table->dateTime('gdu_created_on');
            $table->bigInteger('gdu_updated_by')->nullable();
            $table->dateTime('gdu_updated_on')->nullable();
            $table->bigInteger('gdu_deleted_by')->nullable();
            $table->dateTime('gdu_deleted_on')->nullable();
            $table->unique(['gdu_gd_id','gdu_uom_based_id', 'gdu_uom_id'], 'tbl_gdu_gd_id_uom_based_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_unit');
    }
}
