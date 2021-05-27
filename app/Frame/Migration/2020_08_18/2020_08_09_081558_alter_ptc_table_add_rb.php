<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPtcTableAddRb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->bigInteger('ptc_rb_id')->unsigned()->nullable();
            $table->foreign('ptc_rb_id', 'tbl_ptc_rb_id_foreign')->references('rb_id')->on('relation_bank');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->dropForeign('tbl_ptc_rb_id_foreign');
            $table->dropColumn('ptc_rb_id');
        });
    }
}
