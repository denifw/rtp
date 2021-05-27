<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOutboundDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_outbound_detail', function (Blueprint $table) {
            $table->bigIncrements('jod_id');
            $table->bigInteger('jod_job_id')->unsigned();
            $table->foreign('jod_job_id', 'tbl_jod_job_id_foreign')->references('job_id')->on('job_outbound');
            $table->bigInteger('jod_jog_id')->unsigned();
            $table->foreign('jod_jog_id', 'tbl_jod_jog_id_foreign')->references('jog_id')->on('job_goods');
            $table->bigInteger('jod_jid_id')->unsigned();
            $table->foreign('jod_jid_id', 'tbl_jod_jid_id_foreign')->references('jid_id')->on('job_inbound_detail');
            $table->float('jod_quantity');
            $table->bigInteger('jod_uom_id')->unsigned();
            $table->foreign('jod_uom_id', 'tbl_jod_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('jod_jis_id')->unsigned()->nullable();
            $table->foreign('jod_jis_id', 'tbl_jod_jis_id_foreign')->references('jis_id')->on('job_inbound_stock');
            $table->bigInteger('jod_created_by');
            $table->dateTime('jod_created_on');
            $table->bigInteger('jod_updated_by')->nullable();
            $table->dateTime('jod_updated_on')->nullable();
            $table->bigInteger('jod_deleted_by')->nullable();
            $table->dateTime('jod_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_outbound_detail');
    }
}
