<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_token', function (Blueprint $table) {
            $table->uuid('ut_id')->primary();
            $table->string('ut_token', 256);
            $table->string('ut_type', 128);
            $table->uuid('ut_us_id')->unsigned();
            $table->foreign('ut_us_id', 'tbl_ut_us_id_fkey')->references('us_id')->on('users');
            $table->uuid('ut_ss_id')->unsigned();
            $table->foreign('ut_ss_id', 'tbl_ut_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->dateTime('ut_expired_on');
            $table->dateTime('ut_deleted_on')->nullable();
            $table->unique('ut_token', 'tbl_ut_token_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_token');
    }
}
