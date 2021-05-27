<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankTransactionApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transaction_approval', function (Blueprint $table) {
            $table->bigIncrements('bta_id');
            $table->bigInteger('bta_bt_id')->unsigned();
            $table->foreign('bta_bt_id', 'tbl_bta_bt_id_fkey')->references('bt_id')->on('bank_transaction');
            $table->bigInteger('bta_created_by');
            $table->dateTime('bta_created_on');
            $table->bigInteger('bta_updated_by')->nullable();
            $table->dateTime('bta_updated_on')->nullable();
            $table->bigInteger('bta_deleted_by')->nullable();
            $table->dateTime('bta_deleted_on')->nullable();
            $table->string('bta_deleted_reason', 256)->nullable();
            $table->uuid('bta_uid');
            $table->unique('bta_uid', 'tbl_bta_uid_unique');
        });

        Schema::table('bank_transaction', function (Blueprint $table) {
            $table->bigInteger('bt_bta_id')->unsigned()->nullable();
            $table->foreign('bt_bta_id', 'tbl_bt_bta_id_fkey')->references('bta_id')->on('bank_transaction_approval');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_transaction', function (Blueprint $table) {
            $table->dropForeign('tbl_bt_bta_id_fkey');
            $table->dropColumn('bt_bta_id');
        });
        Schema::dropIfExists('bank_transaction_approval');
    }
}
