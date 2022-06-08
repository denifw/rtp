<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupRightTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_right', function (Blueprint $table) {
            $table->uuid('ugr_id');
            $table->uuid('ugr_usg_id')->unsigned();
            $table->foreign('ugr_usg_id', 'tbl_ugr_usg_id_fkey')->references('usg_id')->on('user_group');
            $table->uuid('ugr_pr_id')->unsigned();
            $table->foreign('ugr_pr_id', 'tbl_ugr_pr_id_fkey')->references('pr_id')->on('page_right');
            $table->dateTime('ugr_created_on');
            $table->uuid('ugr_created_by');
            $table->dateTime('ugr_updated_on')->nullable();
            $table->uuid('ugr_updated_by')->nullable();
            $table->dateTime('ugr_deleted_on')->nullable();
            $table->uuid('ugr_deleted_by')->nullable();
            $table->string('ugr_deleted_reason', 256)->nullable();
            $table->unique(['ugr_usg_id', 'ugr_pr_id'], 'tbl_ugr_usg_id_pr_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupRightSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_right');
    }
}
