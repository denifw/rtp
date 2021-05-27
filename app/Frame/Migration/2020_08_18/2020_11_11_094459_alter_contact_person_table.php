<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterContactPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_person', function (Blueprint $table) {
            $table->bigInteger('cp_salutation_id')->unsigned()->nullable();
            $table->foreign('cp_salutation_id', 'tbl_cp_salutation_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('cp_jbt_id')->unsigned()->nullable();
            $table->foreign('cp_jbt_id', 'tbl_cp_jbt_id_foreign')->references('jbt_id')->on('job_title');
            $table->bigInteger('cp_dpt_id')->unsigned()->nullable();
            $table->foreign('cp_dpt_id', 'tbl_cp_dpt_id_foreign')->references('dpt_id')->on('department');
            $table->date('cp_birthday')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_person', function (Blueprint $table) {
            $table->dropForeign('tbl_cp_salutation_id_foreign');
            $table->dropColumn('cp_salutation_id');
            $table->dropForeign('tbl_cp_jbt_id_foreign');
            $table->dropColumn('cp_jbt_id');
            $table->dropForeign('tbl_cp_dpt_id_foreign');
            $table->dropColumn('cp_dpt_id');
            $table->dropColumn('cp_birthday');
            $table->dropColumn('cp_deleted_reason');
        });
    }
}
