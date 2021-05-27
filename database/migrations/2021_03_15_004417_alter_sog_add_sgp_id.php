<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSogAddSgpId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_goods', function (Blueprint $table) {
            $table->bigInteger('sog_sgp_id')->unsigned()->nullable();
            $table->foreign('sog_sgp_id', 'tbl_sog_sgp_id_foreign')->references('sgp_id')->on('sales_goods_position');
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
