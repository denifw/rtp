<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJopAddSos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_purchase', function (Blueprint $table) {
            $table->bigInteger('jop_sosl_id')->unsigned()->nullable();
            $table->foreign('jop_sosl_id', 'tbl_jop_sosl_id_foreign')->references('sosl_id')->on('sales_order_sales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_purchase', function (Blueprint $table) {
            $table->dropForeign('tbl_jop_sosl_id_foreign');
            $table->dropColumn('jop_sosl_id');
        });
    }
}
