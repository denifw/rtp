<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSogRemoveJogId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_goods', function (Blueprint $table) {
            $table->dropForeign('tbl_sog_jog_id_foreign');
            $table->dropColumn('sog_jog_id');
        });
        Schema::table('sales_order_container', function (Blueprint $table) {
            $table->dropForeign('tbl_soc_joc_id_foreign');
            $table->dropColumn('soc_joc_id');
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
