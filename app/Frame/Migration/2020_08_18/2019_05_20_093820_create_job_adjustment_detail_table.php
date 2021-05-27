<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobAdjustmentDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_adjustment_detail', function (Blueprint $table) {
            $table->bigIncrements('jad_id');
            $table->bigInteger('jad_ja_id')->unsigned();
            $table->foreign('jad_ja_id', 'tbl_jad_ja_id_foreign')->references('ja_id')->on('job_adjustment');
            $table->bigInteger('jad_jid_id')->unsigned();
            $table->foreign('jad_jid_id', 'tbl_jad_jid_id_foreign')->references('jid_id')->on('job_inbound_detail');
            $table->float('jad_quantity');
            $table->bigInteger('jad_uom_id')->unsigned();
            $table->foreign('jad_uom_id', 'tbl_jad_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('jad_sat_id')->unsigned();
            $table->foreign('jad_sat_id', 'tbl_jad_sat_id_foreign')->references('sat_id')->on('stock_adjustment_type');
            $table->string('jad_remark', 255)->nullable();
            $table->bigInteger('jad_jis_id')->unsigned()->nullable();
            $table->foreign('jad_jis_id', 'tbl_jad_jis_id_foreign')->references('jis_id')->on('job_inbound_stock');
            $table->bigInteger('jad_created_by');
            $table->dateTime('jad_created_on');
            $table->bigInteger('jad_updated_by')->nullable();
            $table->dateTime('jad_updated_on')->nullable();
            $table->bigInteger('jad_deleted_by')->nullable();
            $table->dateTime('jad_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_adjustment_detail');
    }
}
