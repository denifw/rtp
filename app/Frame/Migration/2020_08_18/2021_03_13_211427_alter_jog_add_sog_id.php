<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJogAddSogId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_goods', function (Blueprint $table) {
            $table->bigInteger('jog_sog_id')->unsigned()->nullable();
            $table->foreign('jog_sog_id', 'tbl_jog_sog_id_fkey')->references('sog_id')->on('sales_order_goods');
            $table->bigInteger('jog_ji_jo_id')->unsigned()->nullable();
            $table->foreign('jog_ji_jo_id', 'tbl_jog_ji_jo_id_fkey')->references('jo_id')->on('job_order');
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
