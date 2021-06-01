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
            $table->uuid('ugp_id')->primary();
            $table->uuid('ugp_usg_id')->unsigned();
            $table->foreign('ugp_usg_id', 'tbl_ugp_usg_id_fkey')->references('usg_id')->on('user_group');
            $table->uuid('ugp_pg_id')->unsigned();
            $table->foreign('ugp_pg_id', 'tbl_ugp_pg_id_fkey')->references('pg_id')->on('page');
            $table->dateTime('ugp_created_on');
            $table->uuid('ugp_created_by');
            $table->dateTime('ugp_updated_on')->nullable();
            $table->uuid('ugp_updated_by')->nullable();
            $table->dateTime('ugp_deleted_on')->nullable();
            $table->uuid('ugp_deleted_by')->nullable();
            $table->string('ugp_deleted_reason', 256)->nullable();
            $table->unique(['ugp_usg_id', 'ugp_pg_id'], 'tbl_ugp_usg_id_pg_id_unique');
        });
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
