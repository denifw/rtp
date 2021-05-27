<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEqTblAddRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('eq_transport');
            $table->float('eq_length')->nullable();
            $table->float('eq_width')->nullable();
            $table->float('eq_height')->nullable();
            $table->float('eq_volume')->nullable();
            $table->float('eq_weight')->nullable();
            $table->float('eq_lgh_capacity')->nullable();
            $table->float('eq_wdh_capacity')->nullable();
            $table->float('eq_hgh_capacity')->nullable();
            $table->float('eq_cbm_capacity')->nullable();
            $table->float('eq_wgh_capacity')->nullable();
            $table->bigInteger('eq_rel_id')->unsigned()->nullable();
            $table->foreign('eq_rel_id', 'tbl_eq_rel_id_foreign')->references('rel_id')->on('relation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->char('eq_transport', 1)->nullable();
            $table->dropForeign('tbl_eq_rel_id_foreign');
            $table->dropColumn('eq_rel_id');
            $table->dropColumn('eq_length');
            $table->dropColumn('eq_width');
            $table->dropColumn('eq_height');
            $table->dropColumn('eq_volume');
            $table->dropColumn('eq_weight');
            $table->dropColumn('eq_lgh_capacity');
            $table->dropColumn('eq_wdh_capacity');
            $table->dropColumn('eq_hgh_capacity');
            $table->dropColumn('eq_cbm_capacity');
            $table->dropColumn('eq_wgh_capacity');
        });
    }
}
