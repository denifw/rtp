<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJopTableAddJos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_purchase', function (Blueprint $table) {
            $table->dropForeign('tbl_jop_sosl_id_foreign');
            $table->dropColumn('jop_sosl_id');
            $table->bigInteger('jop_jos_id')->unsigned()->nullable();
            $table->foreign('jop_jos_id', 'tbl_jop_jos_id_foreign')->references('jos_id')->on('job_sales');
            $table->float('jop_total')->nullable();
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
