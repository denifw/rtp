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
            $table->bigIncrements('us_id');
            $table->string('us_name', 255);
            $table->string('us_username', 255);
            $table->string('us_password');
            $table->char('us_system', 1)->default('N');
            $table->string('us_picture', 225)->nullable();
            $table->char('us_allow_mail', 1)->default('Y');
            $table->bigInteger('us_lg_id')->unsigned();
            $table->foreign('us_lg_id', 'tbl_us_lg_id_foreign')->references('lg_id')->on('languages');
            $table->string('us_menu_style')->nullable();
            $table->char('us_confirm', 1)->default('N');
            $table->char('us_active', 1)->default('Y');
            $table->bigInteger('us_created_by');
            $table->dateTime('us_created_on');
            $table->bigInteger('us_updated_by')->nullable();
            $table->dateTime('us_updated_on')->nullable();
            $table->bigInteger('us_deleted_by')->nullable();
            $table->dateTime('us_deleted_on')->nullable();
            $table->unique('us_username', 'tbl_us_username_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserSeeder::class,
        ]);

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
