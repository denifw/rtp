<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceOrderCostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_order_cost', function (Blueprint $table) {
            $table->bigIncrements('svc_id');
            $table->bigInteger('svc_svo_id')->unsigned()->nullable();
            $table->foreign('svc_svo_id', 'tbl_svc_svo_id_foreign')->references('svo_id')->on('service_order');
            $table->bigInteger('svc_svd_id')->unsigned()->nullable();
            $table->foreign('svc_svd_id', 'tbl_svc_svd_id_foreign')->references('svd_id')->on('service_order_detail');
            $table->bigInteger('svc_cc_id')->unsigned();
            $table->foreign('svc_cc_id', 'tbl_svc_cc_id')->references('cc_id')->on('cost_code');
            $table->bigInteger('svc_rel_id')->unsigned();
            $table->foreign('svc_rel_id', 'tbl_svc_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('svc_description', 150);
            $table->float('svc_rate');
            $table->float('svc_quantity');
            $table->bigInteger('svc_uom_id')->unsigned();
            $table->foreign('svc_uom_id', 'tbl_svc_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('svc_tax_id')->unsigned();
            $table->foreign('svc_tax_id', 'tbl_svc_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('svc_total');
            $table->bigInteger('svc_created_by');
            $table->dateTime('svc_created_on');
            $table->bigInteger('svc_updated_by')->nullable();
            $table->dateTime('svc_updated_on')->nullable();
            $table->bigInteger('svc_deleted_by')->nullable();
            $table->dateTime('svc_deleted_on')->nullable();
            $table->unique('svc_svd_id', 'tbl_svc_svd_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_order_cost');
    }
}
