<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateStockOpnameDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opname_detail', function (Blueprint $table) {
            $table->bigIncrements('sod_id');
            $table->bigInteger('sod_sop_id')->unsigned();
            $table->foreign('sod_sop_id', 'tbl_sod_sop_id_foreign')->references('sop_id')->on('stock_opname');
            $table->bigInteger('sod_whs_id')->unsigned();
            $table->foreign('sod_whs_id', 'tbl_sod_whs_id_foreign')->references('whs_id')->on('warehouse_storage');
            $table->bigInteger('sod_gd_id')->unsigned();
            $table->foreign('sod_gd_id', 'tbl_sod_gd_id_foreign')->references('gd_id')->on('goods');
            $table->string('sod_production_number', 255)->nullable();
            $table->bigInteger('sod_gdt_id')->unsigned()->nullable();
            $table->foreign('sod_gdt_id', 'tbl_sod_gdt_id_foreign')->references('gdt_id')->on('goods_damage_type');
            $table->float('sod_quantity');
            $table->float('sod_qty_figure')->nullable();
            $table->bigInteger('sod_gdu_id')->unsigned();
            $table->foreign('sod_gdu_id', 'tbl_sod_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
            $table->string('sod_remark', 255)->nullable();
            $table->bigInteger('sod_created_by');
            $table->dateTime('sod_created_on');
            $table->bigInteger('sod_updated_by')->nullable();
            $table->dateTime('sod_updated_on')->nullable();
            $table->bigInteger('sod_deleted_by')->nullable();
            $table->dateTime('sod_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_opname_detail');

    }
}
