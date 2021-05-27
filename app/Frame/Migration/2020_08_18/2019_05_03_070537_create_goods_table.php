<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->bigIncrements('gd_id');
            $table->bigInteger('gd_ss_id')->unsigned();
            $table->foreign('gd_ss_id', 'tbl_gd_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('gd_rel_id')->unsigned();
            $table->foreign('gd_rel_id', 'tbl_gd_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('gd_gdc_id')->unsigned();
            $table->foreign('gd_gdc_id', 'tbl_gd_gdc_id_foreign')->references('gdc_id')->on('goods_category');
            $table->bigInteger('gd_br_id')->unsigned();
            $table->foreign('gd_br_id', 'tbl_gd_br_id_foreign')->references('br_id')->on('brand');
            $table->bigInteger('gd_uom_id')->unsigned();
            $table->foreign('gd_uom_id', 'tbl_gd_uom_id_foreign')->references('uom_id')->on('unit');
            $table->string('gd_sku', 255);
            $table->string('gd_name', 255);
            $table->string('gd_description', 255)->nullable();
            $table->char('gd_stackable', 1)->default('Y');
            $table->bigInteger('gd_stackable_amount')->nullable();
            $table->float('gd_minimum_temperature')->nullable();
            $table->float('gd_maximum_temperature')->nullable();
            $table->float('gd_minimum_stock')->nullable();
            $table->float('gd_maximum_stock')->nullable();
            $table->float('gd_length')->nullable();
            $table->float('gd_width')->nullable();
            $table->float('gd_height')->nullable();
            $table->float('gd_volume')->nullable();
            $table->float('gd_net_weight')->nullable();
            $table->float('gd_gross_weight')->nullable();
            $table->char('gd_active', 1)->default('Y');
            $table->bigInteger('gd_created_by');
            $table->dateTime('gd_created_on');
            $table->bigInteger('gd_updated_by')->nullable();
            $table->dateTime('gd_updated_on')->nullable();
            $table->bigInteger('gd_deleted_by')->nullable();
            $table->dateTime('gd_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods');
    }
}
