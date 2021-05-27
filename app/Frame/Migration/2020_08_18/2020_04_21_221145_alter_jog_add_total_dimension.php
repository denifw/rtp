<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJogAddTotalDimension extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_goods', function (Blueprint $table) {
            $table->float('jog_total_cbm')->nullable();
            $table->float('jog_total_tonnage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_goods', function (Blueprint $table) {
            $table->dropColumn('jog_total_cbm');
            $table->dropColumn('jog_total_tonnage');
        });
    }
}
