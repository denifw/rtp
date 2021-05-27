<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateCashAdvanceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance_detail', function (Blueprint $table) {
            $table->bigIncrements('cad_id');
            $table->bigInteger('cad_ca_id')->unsigned();
            $table->foreign('cad_ca_id', 'tbl_cad_ca_id_foreign')->references('ca_id')->on('cash_advance');
            $table->bigInteger('cad_jop_id')->unsigned()->nullable();
            $table->foreign('cad_jop_id', 'tbl_cad_jop_id_foreign')->references('jop_id')->on('job_purchase');
            $table->bigInteger('cad_cc_id')->unsigned()->nullable();
            $table->foreign('cad_cc_id', 'tbl_cad_cc_id_foreign')->references('cc_id')->on('cost_code');
            $table->string('cad_description', 256)->nullable();
            $table->float('cad_quantity')->nullable();
            $table->bigInteger('cad_uom_id')->unsigned()->nullable();
            $table->foreign('cad_uom_id', 'tbl_cad_uom_id_foreign')->references('uom_id')->on('unit');
            $table->float('cad_rate')->nullable();
            $table->bigInteger('cad_cur_id')->unsigned()->nullable();
            $table->foreign('cad_cur_id', 'tbl_cad_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('cad_exchange_rate')->nullable();
            $table->bigInteger('cad_tax_id')->unsigned()->nullable();
            $table->foreign('cad_tax_id', 'tbl_cad_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('cad_total')->nullable();
            $table->bigInteger('cad_doc_id')->unsigned()->nullable();
            $table->foreign('cad_doc_id', 'tbl_cad_doc_id_foreign')->references('doc_id')->on('document');
            $table->char('cad_ea_payment', 1)->default('N');
            $table->bigInteger('cad_created_by');
            $table->dateTime('cad_created_on');
            $table->bigInteger('cad_updated_by')->nullable();
            $table->dateTime('cad_updated_on')->nullable();
            $table->bigInteger('cad_deleted_by')->nullable();
            $table->dateTime('cad_deleted_on')->nullable();
            $table->string('cad_deleted_reason', 256)->nullable();
            $table->uuid('cad_uid');
            $table->unique('cad_uid', 'tbl_cad_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_advance_detail');
    }
}
