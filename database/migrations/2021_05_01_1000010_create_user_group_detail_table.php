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
            $table->uuid('ugd_id')->primary();
            $table->uuid('ugd_usg_id')->unsigned();
            $table->foreign('ugd_usg_id', 'tbl_ugd_usg_id_fkey')->references('usg_id')->on('user_group');
            $table->uuid('ugd_ump_id')->unsigned();
            $table->foreign('ugd_ump_id', 'tbl_ugd_ump_id_fkey')->references('ump_id')->on('user_mapping');
            $table->dateTime('ugd_created_on');
            $table->uuid('ugd_created_by');
            $table->dateTime('ugd_updated_on')->nullable();
            $table->uuid('ugd_updated_by')->nullable();
            $table->dateTime('ugd_deleted_on')->nullable();
            $table->uuid('ugd_deleted_by')->nullable();
            $table->string('ugd_deleted_reason', 256)->nullable();
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
