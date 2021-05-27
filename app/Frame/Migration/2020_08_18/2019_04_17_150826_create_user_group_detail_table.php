<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_detail', function (Blueprint $table) {
            $table->bigIncrements('ugd_id');
            $table->bigInteger('ugd_usg_id')->unsigned();
            $table->foreign('ugd_usg_id', 'tbl_ugd_usg_id_foreign')->references('usg_id')->on('user_group');
            $table->bigInteger('ugd_ump_id')->unsigned();
            $table->foreign('ugd_ump_id', 'tbl_ugd_ump_id_foreign')->references('ump_id')->on('user_mapping');
            $table->dateTime('ugd_created_on');
            $table->bigInteger('ugd_created_by');
            $table->dateTime('ugd_updated_on')->nullable();
            $table->bigInteger('ugd_updated_by')->nullable();
            $table->dateTime('ugd_deleted_on')->nullable();
            $table->bigInteger('ugd_deleted_by')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupDetailSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_detail');
    }
}
