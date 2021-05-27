<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInvoiceAddCurrency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_invoice', function (Blueprint $table) {
            $table->bigInteger('si_cur_id')->unsigned();
            $table->foreign('si_cur_id', 'tbl_si_cur_id_fkey')->references('cur_id')->on('currency');
            $table->float('si_exchange_rate')->nullable();
        });
        Schema::table('purchase_invoice', function (Blueprint $table) {
            $table->bigInteger('pi_cur_id')->unsigned();
            $table->foreign('pi_cur_id', 'tbl_pi_cur_id_fkey')->references('cur_id')->on('currency');
            $table->float('pi_exchange_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
