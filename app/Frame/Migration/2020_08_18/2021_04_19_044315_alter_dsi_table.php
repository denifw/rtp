<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDsiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dashboard_item', function (Blueprint $table) {
            $table->dropUnique('tbl_dsi_route_unique');
            $table->dropUnique('tbl_dsi_path_unique');
            $table->bigInteger('dsi_module_id')->nullable();
            $table->foreign('dsi_module_id', 'tbl_dsi_module_id_foreign')->references('sty_id')->on('system_type');
            $table->char('dsi_active', 1)->default('Y');
            $table->jsonb('dsi_parameter')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashboard_item', function (Blueprint $table) {
            $table->dropForeign('tbl_dsi_module_id_foreign');
            $table->dropColumn('dsi_module_id');
            $table->dropColumn('dsi_active');
            $table->dropColumn('dsi_parameter');
        });
    }
}
