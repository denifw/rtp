<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankAccountBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_account_balance', function (Blueprint $table) {
            $table->bigIncrements('bab_id');
            $table->bigInteger('bab_ba_id')->unsigned();
            $table->foreign('bab_ba_id', 'tbl_bab_ba_id_foreign')->references('ba_id')->on('bank_account');
            $table->float('bab_amount');
            $table->bigInteger('bab_created_by');
            $table->dateTime('bab_created_on');
            $table->bigInteger('bab_updated_by')->nullable();
            $table->dateTime('bab_updated_on')->nullable();
            $table->bigInteger('bab_deleted_by')->nullable();
            $table->dateTime('bab_deleted_on')->nullable();
            $table->string('bab_deleted_reason', 256)->nullable();
            $table->uuid('bab_uid');
            $table->unique('bab_uid', 'tbl_bab_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_account_balance');
    }
}
