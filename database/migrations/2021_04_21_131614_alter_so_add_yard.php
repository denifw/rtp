<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSoAddYard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order', function (Blueprint $table) {
            $table->dropForeign('tbl_so_eq_id_foreign');
            $table->dropColumn('so_eq_id');
            $table->renameColumn('so_old_transport', 'so_transport_name');
            $table->bigInteger('so_yp_id')->unsigned()->nullable();
            $table->foreign('so_yp_id', 'tbl_so_yp_id_foreign')->references('of_id')->on('office');
            $table->date('so_yp_date')->nullable();
            $table->time('so_yp_time')->nullable();
            $table->bigInteger('so_yr_id')->unsigned()->nullable();
            $table->foreign('so_yr_id', 'tbl_so_yr_id_foreign')->references('of_id')->on('office');
            $table->date('so_yr_date')->nullable();
            $table->time('so_yr_time')->nullable();
        });
        Schema::table('load_unload_delivery', function (Blueprint $table) {
            $table->bigInteger('lud_rel_id')->unsigned()->nullable(true)->change();
            $table->bigInteger('lud_of_id')->unsigned()->nullable(true)->change();
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
