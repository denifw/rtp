<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEquipmentGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_group', function (Blueprint $table) {
            $table->bigInteger('eg_sty_id')->unsigned()->nullable();
            $table->foreign('eg_sty_id', 'tbl_eg_sty_id_foreign')->references('sty_id')->on('system_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_group', function (Blueprint $table) {
            $table->dropForeign('tbl_eg_sty_id_foreign');
            $table->dropColumn('eg_sty_id');
        });
    }
}
