<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupPageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_page', function (Blueprint $table) {
            $table->bigIncrements('ugp_id');
            $table->bigInteger('ugp_usg_id')->unsigned();
            $table->foreign('ugp_usg_id', 'tbl_ugp_usg_id_foreign')->references('usg_id')->on('user_group');
            $table->bigInteger('ugp_pg_id')->unsigned();
            $table->foreign('ugp_pg_id', 'tbl_ugp_pg_id_foreign')->references('pg_id')->on('page');
            $table->dateTime('ugp_created_on');
            $table->bigInteger('ugp_created_by');
            $table->dateTime('ugp_updated_on')->nullable();
            $table->bigInteger('ugp_updated_by')->nullable();
            $table->dateTime('ugp_deleted_on')->nullable();
            $table->bigInteger('ugp_deleted_by')->nullable();
            $table->unique(['ugp_usg_id', 'ugp_pg_id'], 'tbl_ugp_usg_id_pg_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupPageSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_page');
    }
}
