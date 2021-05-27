<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQtTableAddQts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotation', function (Blueprint $table) {
            $table->bigInteger('qt_qts_id')->unsigned()->nullable();
            $table->foreign('qt_qts_id', 'tbl_qt_qts_id_fkey')->references('qts_id')->on('quotation_submit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotation', function (Blueprint $table) {
            $table->dropForeign('tbl_qt_qts_id_fkey');
            $table->dropColumn('qt_qts_id');
        });
    }
}
