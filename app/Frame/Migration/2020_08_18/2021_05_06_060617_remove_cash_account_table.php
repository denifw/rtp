<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCashAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_invoice', function (Blueprint $table) {
            $table->dropForeign('tbl_pi_ca_id_foreign');
            $table->dropColumn('pi_ca_id');
        });
        Schema::table('cash_advance', function (Blueprint $table) {
            $table->dropForeign('tbl_ca_ptc_id_fkey');
            $table->dropColumn('ca_ptc_id');
            $table->dropForeign('tbl_ca_carc_id_foreign');
            $table->dropColumn('ca_carc_id');
            $table->dropForeign('tbl_ca_cart_id_foreign');
            $table->dropColumn('ca_cart_id');
        });
        Schema::dropIfExists('cash_advance_detail');
        Schema::dropIfExists('cash_advance_received');
        Schema::dropIfExists('cash_advance_returned');
        Schema::dropIfExists('cash_advance');
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->dropForeign('tbl_ptc_pcr_id_foreign');
            $table->dropColumn('ptc_pcr_id');
        });
        Schema::dropIfExists('petty_cash_request');
        Schema::dropIfExists('petty_cash');
        Schema::dropIfExists('cash_balance');
        Schema::dropIfExists('cash_account');
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
