<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupDashboardItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_dashboard_item', function (Blueprint $table) {
            $table->bigIncrements('ugds_id');
            $table->bigInteger('ugds_dsi_id')->unsigned();
            $table->foreign('ugds_dsi_id', 'tbl_ugds_dsi_id_foreign')->references('dsi_id')->on('dashboard_item');
            $table->bigInteger('ugds_usg_id')->unsigned();
            $table->foreign('ugds_usg_id', 'tbl_ugds_usg_id_foreign')->references('usg_id')->on('user_group');
            $table->bigInteger('ugds_created_by');
            $table->dateTime('ugds_created_on');
            $table->bigInteger('ugds_updated_by')->nullable();
            $table->dateTime('ugds_updated_on')->nullable();
            $table->bigInteger('ugds_deleted_by')->nullable();
            $table->dateTime('ugds_deleted_on')->nullable();
            $table->unique(['ugds_dsi_id', 'ugds_usg_id'], 'tbl_ugds_dsi_id_usg_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupDashboardItemSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_dashboard_item');
    }
}
