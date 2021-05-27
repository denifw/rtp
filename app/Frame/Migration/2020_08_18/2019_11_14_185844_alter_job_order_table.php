<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_order', function (Blueprint $table) {
            $table->bigInteger('jo_manager_id')->unsigned()->nullable()->change();
            $table->renameColumn('jo_contract_ref', 'jo_aju_ref');
            $table->dropColumn('jo_active');
            $table->bigInteger('jo_so_id')->unsigned()->nullable();
            $table->foreign('jo_so_id', 'tbl_jo_so_id_foreign')->references('so_id')->on('sales_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_order', function (Blueprint $table) {
            $table->renameColumn('jo_aju_ref', 'jo_contract_ref');
            $table->char('jo_active', 1)->nullable();
            $table->dropForeign('tbl_jo_so_id_foreign');
            $table->dropColumn('jo_so_id');
        });
    }
}
