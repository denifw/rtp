<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreatePriceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_detail', function (Blueprint $table) {
            $table->bigIncrements('prd_id');

            $table->bigInteger('prd_prc_id')->unsigned();
            $table->foreign('prd_prc_id', 'tbl_prd_prc_id_foreign')->references('prc_id')->on('price');
            $table->bigInteger('prd_cc_id')->unsigned()->nullable();
            $table->foreign('prd_cc_id', 'tbl_prd_cc_id_foreign')->references('cc_id')->on('cost_code');
            $table->string('prd_description', 256);
            $table->float('prd_quantity')->nullable();
            $table->bigInteger('prd_uom_id')->unsigned()->nullable();
            $table->foreign('prd_uom_id', 'tbl_prd_uom_id_foreign')->references('uom_id')->on('unit');
            $table->float('prd_rate');
            $table->float('prd_minimum_rate')->nullable();
            $table->bigInteger('prd_cur_id')->unsigned();
            $table->foreign('prd_cur_id', 'tbl_prd_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('prd_exchange_rate');
            $table->bigInteger('prd_tax_id')->unsigned()->nullable();
            $table->foreign('prd_tax_id', 'tbl_prd_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('prd_total')->nullable();
            $table->string('prd_remark', 256)->nullable();

            $table->bigInteger('prd_created_by');
            $table->dateTime('prd_created_on');
            $table->bigInteger('prd_updated_by')->nullable();
            $table->dateTime('prd_updated_on')->nullable();
            $table->bigInteger('prd_deleted_by')->nullable();
            $table->dateTime('prd_deleted_on')->nullable();
            $table->string('prd_deleted_reason', 256)->nullable();
            $table->uuid('prd_uid');
            $table->unique('prd_uid', 'tbl_prd_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_detail');
    }
}
