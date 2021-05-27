<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMobileTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('us_api_token', 32)->nullable()->change();
        });
        Schema::create('user_mobile_token', function (Blueprint $table) {
            $table->bigIncrements('umt_id');
            $table->bigInteger('umt_us_id')->unsigned();
            $table->foreign('umt_us_id', 'tbl_umt_us_id_foreign')->references('us_id')->on('users');
            $table->string('umt_api_token', 255)->nullable();
            $table->dateTime('umt_created_on');
            $table->dateTime('umt_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_mobile_token');
    }
}
