<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalOrderCostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewal_order_cost', function (Blueprint $table) {
            $table->bigIncrements('rnc_id');
            $table->bigInteger('rnc_rno_id')->unsigned()->nullable();
            $table->foreign('rnc_rno_id', 'tbl_rnc_rno_id_foreign')->references('rno_id')->on('renewal_order');
            $table->bigInteger('rnc_rnd_id')->unsigned()->nullable();
            $table->foreign('rnc_rnd_id', 'tbl_rnc_rnd_id_foreign')->references('rnd_id')->on('renewal_order_detail');
            $table->bigInteger('rnc_cc_id')->unsigned();
            $table->foreign('rnc_cc_id', 'tbl_rnc_cc_id')->references('cc_id')->on('cost_code');
            $table->bigInteger('rnc_rel_id')->unsigned();
            $table->foreign('rnc_rel_id', 'tbl_rnc_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('rnc_description', 150);
            $table->float('rnc_rate');
            $table->float('rnc_quantity');
            $table->bigInteger('rnc_uom_id')->unsigned();
            $table->foreign('rnc_uom_id', 'tbl_rnc_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('rnc_tax_id')->unsigned();
            $table->foreign('rnc_tax_id', 'tbl_rnc_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('rnc_total');
            $table->bigInteger('rnc_created_by');
            $table->dateTime('rnc_created_on');
            $table->bigInteger('rnc_updated_by')->nullable();
            $table->dateTime('rnc_updated_on')->nullable();
            $table->bigInteger('rnc_deleted_by')->nullable();
            $table->dateTime('rnc_deleted_on')->nullable();
            $table->unique('rnc_rnd_id', 'tbl_rnc_rnd_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewal_order_cost');
    }
}
