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
            $table->uuid('bab_id')->primary();
            $table->uuid('bab_ba_id')->unsigned();
            $table->foreign('bab_ba_id', 'tbl_bab_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->float('bab_amount');
            $table->uuid('bab_created_by');
            $table->dateTime('bab_created_on');
            $table->uuid('bab_updated_by')->nullable();
            $table->dateTime('bab_updated_on')->nullable();
            $table->uuid('bab_deleted_by')->nullable();
            $table->dateTime('bab_deleted_on')->nullable();
            $table->string('bab_deleted_reason', 256)->nullable();
        });
        Schema::table('bank_account', function (Blueprint $table) {
            $table->uuid('ba_bab_id')->unsigned()->nullable();
            $table->foreign('ba_bab_id', 'tbl_ba_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_account', function (Blueprint $table) {
            $table->dropForeign('tbl_ba_bab_id_fkey');
            $table->dropColumn('ba_bab_id');
        });
        Schema::dropIfExists('bank_account_balance');
    }
}
