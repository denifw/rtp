<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobDeliveryDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_delivery_detail', function (Blueprint $table) {
            $table->bigIncrements('jdld_id');
            $table->bigInteger('jdld_jdl_id')->unsigned();
            $table->foreign('jdld_jdl_id', 'tbl_jdld_jdl_id_fkey')->references('jdl_id')->on('job_delivery');
            $table->bigInteger('jdld_soc_id')->unsigned();
            $table->foreign('jdld_soc_id', 'tbl_jdld_soc_id_fkey')->references('soc_id')->on('sales_order_container');
            $table->char('jdld_final_destination', 1);
            $table->bigInteger('jdld_created_by');
            $table->dateTime('jdld_created_on');
            $table->bigInteger('jdld_updated_by')->nullable();
            $table->dateTime('jdld_updated_on')->nullable();
            $table->bigInteger('jdld_deleted_by')->nullable();
            $table->dateTime('jdld_deleted_on')->nullable();
            $table->string('jdld_deleted_reason', 256)->nullable();
            $table->uuid('jdld_uid');
            $table->unique('jdld_uid', 'tbl_jdld_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_delivery_detail');
    }
}
