<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('us_id')->primary();
            $table->string('us_name', 256);
            $table->string('us_username', 256);
            $table->string('us_password', 256);
            $table->string('us_api_token', 64)->nullable()->change();
            $table->char('us_system', 1)->default('N');
            $table->string('us_picture', 225)->nullable();
            $table->uuid('us_lg_id')->unsigned();
            $table->foreign('us_lg_id', 'tbl_us_lg_id_fkey')->references('lg_id')->on('languages');
            $table->string('us_menu_style')->nullable();
            $table->char('us_confirm', 1)->default('N');
            $table->char('us_active', 1)->default('Y');
            $table->uuid('us_created_by');
            $table->dateTime('us_created_on');
            $table->uuid('us_updated_by')->nullable();
            $table->dateTime('us_updated_on')->nullable();
            $table->uuid('us_deleted_by')->nullable();
            $table->dateTime('us_deleted_on')->nullable();
            $table->string('us_deleted_reason', 256)->nullable();
            $table->unique('us_username', 'tbl_us_username_unique');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
