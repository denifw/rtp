<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobFinanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_sales', function (Blueprint $table) {
            $table->dropForeign('tbl_jos_qtnd_id_foreign');
            $table->dropColumn('jos_qtnd_id');
            $table->dropColumn('jos_minimum_rate');
        });
        Schema::table('job_purchase', function (Blueprint $table) {
            $table->dropForeign('tbl_jop_qtnd_id_foreign');
            $table->dropColumn('jop_qtnd_id');
            $table->dropColumn('jop_minimum_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_sales', function (Blueprint $table) {
            $table->float('jos_minimum_rate')->nullable();
            $table->bigInteger('jos_qtnd_id')->unsigned()->nullable();
            $table->foreign('jos_qtnd_id', 'tbl_jos_qtnd_id_foreign')->references('qtnd_id')->on('quotation_detail');
        });
        Schema::table('job_purchase', function (Blueprint $table) {
            $table->float('jop_minimum_rate')->nullable();
            $table->bigInteger('jop_qtnd_id')->unsigned()->nullable();
            $table->foreign('jop_qtnd_id', 'tbl_jop_qtnd_id_foreign')->references('qtnd_id')->on('quotation_detail');
        });
    }
}
