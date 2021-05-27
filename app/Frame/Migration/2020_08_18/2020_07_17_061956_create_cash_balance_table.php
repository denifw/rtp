<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_balance', function (Blueprint $table) {
            $table->bigIncrements('cb_id');
            $table->bigInteger('cb_cac_id')->unsigned();
            $table->foreign('cb_cac_id', 'tbl_cb_cac_id_foreign')->references('cac_id')->on('cash_account');
            $table->float('cb_amount');
            $table->bigInteger('cb_created_by');
            $table->dateTime('cb_created_on');
            $table->bigInteger('cb_updated_by')->nullable();
            $table->dateTime('cb_updated_on')->nullable();
            $table->bigInteger('cb_deleted_by')->nullable();
            $table->dateTime('cb_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_balance');
    }
}
