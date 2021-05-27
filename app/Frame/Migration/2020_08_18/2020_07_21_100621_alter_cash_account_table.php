<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCashAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_account', function (Blueprint $table) {
            $table->bigInteger('cac_rb_id')->unsigned();
            $table->foreign('cac_rb_id', 'tbl_cac_rb_id_foreign')->references('rb_id')->on('relation_bank');
            $table->dropUnique('tbl_cac_ss_srv_us_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_account', function (Blueprint $table) {
            $table->dropForeign('tbl_cac_rb_id_foreign');
            $table->dropColumn('cac_rb_id');
            $table->unique(['cac_ss_id', 'cac_srv_id', 'cac_us_id'], 'tbl_cac_ss_srv_us_unique');
        });
    }
}
