<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJopAddCa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_purchase', function (Blueprint $table) {
            $table->bigInteger('jop_cad_id')->unsigned()->nullable();
            $table->foreign('jop_cad_id', 'tbl_jop_cad_id_fkey')->references('cad_id')->on('cash_advance_detail');
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
            $table->dropForeign('tbl_jop_cad_id_fkey');
            $table->dropColumn('jop_cad_id');
        });
    }
}
