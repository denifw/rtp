<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaAddTopUp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_advance', function (Blueprint $table) {
            $table->bigInteger('ca_ptc_id')->unsigned()->nullable();
            $table->foreign('ca_ptc_id', 'tbl_ca_ptc_id_fkey')->references('ptc_id')->on('petty_cash');
            $table->dropColumn('ca_return_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
