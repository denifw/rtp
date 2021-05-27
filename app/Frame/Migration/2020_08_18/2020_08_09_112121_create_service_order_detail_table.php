<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_order_detail', function (Blueprint $table) {
            $table->bigIncrements('svd_id');
            $table->bigInteger('svd_svo_id')->unsigned();
            $table->foreign('svd_svo_id', 'tbl_svd_svo_id_foreign')->references('svo_id')->on('service_order');
            $table->bigInteger('svd_svt_id')->unsigned();
            $table->foreign('svd_svt_id', 'tbl_svd_svt_id_foreign')->references('svt_id')->on('service_task');
            $table->float('svd_est_cost');
            $table->string('svd_remark', 255)->nullable();
            $table->bigInteger('svd_created_by');
            $table->dateTime('svd_created_on');
            $table->bigInteger('svd_updated_by')->nullable();
            $table->dateTime('svd_updated_on')->nullable();
            $table->bigInteger('svd_deleted_by')->nullable();
            $table->dateTime('svd_deleted_on')->nullable();
            $table->unique(['svd_svo_id', 'svd_svt_id'], 'tbl_svd_svo_id_svt_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_order_detail');
    }
}
