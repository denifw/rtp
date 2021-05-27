<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateCashAdvanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance', function (Blueprint $table) {
            $table->bigIncrements('ca_id');
            $table->bigInteger('ca_ss_id')->unsigned();
            $table->foreign('ca_ss_id', 'tbl_ca_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('ca_number', 125);
            $table->string('ca_reference', 256)->nullable();
            $table->bigInteger('ca_ba_id')->unsigned();
            $table->foreign('ca_ba_id', 'tbl_ca_ba_id_foreign')->references('ba_id')->on('bank_account');
            $table->bigInteger('ca_ea_id')->unsigned()->nullable();
            $table->foreign('ca_ea_id', 'tbl_ca_ea_id_foreign')->references('ea_id')->on('electronic_account');
            $table->bigInteger('ca_eb_id')->unsigned()->nullable();
            $table->foreign('ca_eb_id', 'tbl_ca_eb_id_foreign')->references('eb_id')->on('electronic_balance');
            $table->float('ca_ea_amount')->nullable();
            $table->bigInteger('ca_cp_id')->unsigned()->nullable();
            $table->foreign('ca_cp_id', 'tbl_ca_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('ca_jo_id')->unsigned()->nullable();
            $table->foreign('ca_jo_id', 'tbl_ca_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->date('ca_date');
            $table->float('ca_amount')->nullable();
            $table->float('ca_reserve_amount')->nullable();
            $table->float('ca_actual_amount')->nullable();
            $table->float('ca_return_amount')->nullable();
            $table->string('ca_notes', 256)->nullable();
            $table->dateTime('ca_receive_on')->nullable();
            $table->bigInteger('ca_receive_by')->unsigned()->nullable();
            $table->foreign('ca_receive_by', 'tbl_ca_receive_by_foreign')->references('us_id')->on('users');
            $table->bigInteger('ca_receive_bab_id')->unsigned()->nullable();
            $table->foreign('ca_receive_bab_id', 'tbl_ca_receive_bab_id_foreign')->references('bab_id')->on('bank_account_balance');
            $table->bigInteger('ca_settlement_by')->unsigned()->nullable();
            $table->foreign('ca_settlement_by', 'tbl_ca_settlement_by_foreign')->references('us_id')->on('users');
            $table->dateTime('ca_settlement_on')->nullable();
            $table->bigInteger('ca_settlement_bab_id')->unsigned()->nullable();
            $table->foreign('ca_settlement_bab_id', 'tbl_ca_settlement_bab_id_foreign')->references('bab_id')->on('bank_account_balance');
            $table->bigInteger('ca_synchronize_by')->nullable();
            $table->foreign('ca_synchronize_by', 'tbl_ca_synchronize_by_foreign')->references('us_id')->on('users');
            $table->dateTime('ca_synchronize_on')->nullable();
            $table->bigInteger('ca_bt_id')->nullable();
            $table->foreign('ca_bt_id', 'tbl_ca_bt_id_foreign')->references('bt_id')->on('bank_transaction');
            $table->bigInteger('ca_created_by');
            $table->dateTime('ca_created_on');
            $table->bigInteger('ca_updated_by')->nullable();
            $table->dateTime('ca_updated_on')->nullable();
            $table->bigInteger('ca_deleted_by')->nullable();
            $table->dateTime('ca_deleted_on')->nullable();
            $table->string('ca_deleted_reason', 256)->nullable();
            $table->uuid('ca_uid');
            $table->unique('ca_uid', 'tbl_ca_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_advance');
    }
}
