<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCcTableAddGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cost_code', function (Blueprint $table) {
            $table->dropColumn('cc_parent');
            $table->dropColumn('cc_type');
            $table->bigInteger('cc_ccg_id')->unsigned();
            $table->foreign('cc_ccg_id', 'tbl_cc_ccg_id_foreign')->references('ccg_id')->on('cost_code_group');
            $table->dropUnique('tbl_cc_ss_id_code_unique');
            $table->unique(['cc_ss_id', 'cc_ccg_id', 'cc_code'], 'tbl_cc_ss_ccg_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cost_code', function (Blueprint $table) {
            $table->dropForeign('tbl_cc_ccg_id_foreign');
            $table->dropUnique('tbl_cc_ss_ccg_code_unique');
            $table->dropColumn('cc_ccg_id');
            $table->bigInteger('cc_parent')->nullable();
            $table->char('cc_type', 1)->nullable();
            $table->unique(['cc_ss_id', 'cc_code'], 'tbl_cc_ss_id_code_unique');
        });
}
}
