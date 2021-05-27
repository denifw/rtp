<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalFulfillmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewal_fulfillment', function (Blueprint $table) {
            $table->bigIncrements('rnf_id');
            $table->bigInteger('rnf_rnrm_id')->unsigned();
            $table->foreign('rnf_rnrm_id', 'tbl_rnf_rnrm_id_foreign')->references('rnrm_id')->on('renewal_reminder');
            $table->bigInteger('rnf_rno_id')->unsigned();
            $table->foreign('rnf_rno_id', 'tbl_rnf_rno_id_foreign')->references('rno_id')->on('renewal_order');
            $table->date('rnf_expiry_date');
            $table->date('rnf_fulfillment_date');
            $table->bigInteger('rnf_created_by');
            $table->dateTime('rnf_created_on');
            $table->bigInteger('rnf_updated_by')->nullable();
            $table->dateTime('rnf_updated_on')->nullable();
            $table->bigInteger('rnf_deleted_by')->nullable();
            $table->dateTime('rnf_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewal_fulfillment');
    }
}
