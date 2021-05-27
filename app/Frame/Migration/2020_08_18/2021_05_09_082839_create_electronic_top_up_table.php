<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElectronicTopUpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('electronic_top_up', function (Blueprint $table) {
            $table->bigIncrements('et_id');
            $table->bigInteger('et_ea_id')->unsigned();
            $table->foreign('et_ea_id', 'tbl_et_ea_id_fkey')->references('ea_id')->on('electronic_account');
            $table->bigInteger('et_ba_id')->unsigned();
            $table->foreign('et_ba_id', 'tbl_et_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->date('et_date');
            $table->float('et_amount');
            $table->string('et_notes', 255)->nullable();
            $table->bigInteger('et_doc_id')->unsigned();
            $table->foreign('et_doc_id', 'tbl_et_doc_id_foreign')->references('doc_id')->on('document');
            $table->bigInteger('et_eb_id')->unsigned();
            $table->foreign('et_eb_id', 'tbl_et_eb_id_fkey')->references('eb_id')->on('electronic_balance');
            $table->bigInteger('et_bab_id')->unsigned();
            $table->foreign('et_bab_id', 'tbl_et_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
            $table->bigInteger('et_created_by');
            $table->dateTime('et_created_on');
            $table->bigInteger('et_updated_by')->nullable();
            $table->dateTime('et_updated_on')->nullable();
            $table->bigInteger('et_deleted_by')->nullable();
            $table->dateTime('et_deleted_on')->nullable();
            $table->string('et_deleted_reason', 256)->nullable();
            $table->uuid('et_uid');
            $table->unique('et_uid', 'tbl_et_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('electronic_top_up');
    }
}
