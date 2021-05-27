<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_notification', function (Blueprint $table) {
            $table->bigIncrements('ugn_id');
            $table->bigInteger('ugn_usg_id')->unsigned();
            $table->foreign('ugn_usg_id', 'tbl_ugn_usg_id_foreign')->references('usg_id')->on('user_group');
            $table->bigInteger('ugn_pn_id')->unsigned();
            $table->foreign('ugn_pn_id', 'tbl_ugn_pn_id_foreign')->references('pn_id')->on('page_notification');
            $table->dateTime('ugn_created_on');
            $table->bigInteger('ugn_created_by');
            $table->dateTime('ugn_updated_on')->nullable();
            $table->bigInteger('ugn_updated_by')->nullable();
            $table->dateTime('ugn_deleted_on')->nullable();
            $table->bigInteger('ugn_deleted_by')->nullable();
            $table->unique(['ugn_usg_id', 'ugn_pn_id'], 'tbl_ugn_usg_id_pn_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupNotificationSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_notification');
    }
}
