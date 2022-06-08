<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupApiAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_api_access', function (Blueprint $table) {
            $table->uuid('uga_id')->primary();
            $table->uuid('uga_usg_id')->unsigned();
            $table->foreign('uga_usg_id', 'tbl_uga_usg_id_fkey')->references('usg_id')->on('user_group');
            $table->uuid('uga_aa_id')->unsigned();
            $table->foreign('uga_aa_id', 'tbl_uga_aa_id_fkey')->references('aa_id')->on('api_access');
            $table->dateTime('uga_created_on');
            $table->uuid('uga_created_by');
            $table->dateTime('uga_updated_on')->nullable();
            $table->uuid('uga_updated_by')->nullable();
            $table->dateTime('uga_deleted_on')->nullable();
            $table->uuid('uga_deleted_by')->nullable();
            $table->string('uga_deleted_reason', 256)->nullable();
            $table->unique(['uga_usg_id', 'uga_aa_id'], 'tbl_uga_usg_id_aa_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupApiSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_api_access');
    }
}
