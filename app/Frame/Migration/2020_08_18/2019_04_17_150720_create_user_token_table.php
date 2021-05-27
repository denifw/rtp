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
            $table->bigIncrements('ut_id');
            $table->string('ut_token', 250);
            $table->string('ut_type', 125);
            $table->bigInteger('ut_us_id')->unsigned();
            $table->foreign('ut_us_id', 'tbl_ut_us_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('ut_ss_id')->unsigned()->nullable();
            $table->foreign('ut_ss_id', 'tbl_ut_ss_id_foreign')->references('ss_id')->on('system_setting');
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
