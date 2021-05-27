<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSoAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order', function (Blueprint $table) {
            $table->char('so_type', 1)->nullable();
            $table->bigInteger('so_relation_id')->unsigned()->nullable();
            $table->foreign('so_relation_id', 'tbl_so_relation_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('so_pic_relation')->unsigned()->nullable();
            $table->foreign('so_pic_relation', 'tbl_so_pic_relation_foreign')->references('cp_id')->on('contact_person');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_order', function (Blueprint $table) {
            $table->dropForeign('tbl_so_relation_id_foreign');
            $table->dropForeign('tbl_so_pic_relation_foreign');
            $table->dropColumn('so_type');
            $table->dropColumn('so_relation_id');
            $table->dropColumn('so_pic_relation');
        });
    }
}
