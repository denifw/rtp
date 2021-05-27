<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDealTableAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deal', function (Blueprint $table) {
            $table->bigInteger('dl_sty_id')->unsigned()->nullable();
            $table->foreign('dl_sty_id', 'tbl_dl_sty_id_foreign')->references('sty_id')->on('system_type');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deal', function (Blueprint $table) {
            $table->dropForeign('tbl_dl_sty_id_foreign');
            $table->dropColumn('dl_sty_id');
        });
    }
}
