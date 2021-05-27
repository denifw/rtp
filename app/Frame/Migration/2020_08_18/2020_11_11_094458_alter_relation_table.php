<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('relation', function (Blueprint $table) {
            $table->bigInteger('rel_manager_id')->unsigned()->nullable();
            $table->foreign('rel_manager_id', 'tbl_rel_manager_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('rel_main_contact_id')->unsigned()->nullable();
            $table->foreign('rel_main_contact_id', 'tbl_rel_main_contact_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('rel_ids_id')->unsigned()->nullable();
            $table->foreign('rel_ids_id', 'tbl_rel_ids_id_foreign')->references('ids_id')->on('industry');
            $table->bigInteger('rel_source_id')->unsigned()->nullable();
            $table->foreign('rel_source_id', 'tbl_rel_source_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('rel_size_id')->unsigned()->nullable();
            $table->foreign('rel_size_id', 'tbl_rel_size_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('rel_established')->nullable();
            $table->bigInteger('rel_employee')->nullable();
            $table->float('rel_revenue')->nullable();
            $table->string('rel_remark', 256)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('relation', function (Blueprint $table) {
            $table->dropForeign('tbl_rel_manager_id_foreign');
            $table->dropColumn('rel_manager_id');
            $table->dropForeign('tbl_rel_main_contact_id_foreign');
            $table->dropColumn('rel_main_contact_id');
            $table->dropForeign('tbl_rel_ids_id_foreign');
            $table->dropColumn('rel_ids_id');
            $table->dropForeign('tbl_rel_source_id_foreign');
            $table->dropColumn('rel_source_id');
            $table->dropForeign('tbl_rel_size_id_foreign');
            $table->dropColumn('rel_size_id');
            $table->dropColumn('rel_established');
            $table->dropColumn('rel_employee');
            $table->dropColumn('rel_revenue');
            $table->dropColumn('rel_deleted_reason');
        });
    }
}
