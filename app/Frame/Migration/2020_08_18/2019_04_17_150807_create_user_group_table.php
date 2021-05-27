<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group', function (Blueprint $table) {
            $table->bigIncrements('usg_id');
            $table->string('usg_name', 125);
            $table->char('usg_active', 1)->default('Y');
            $table->dateTime('usg_created_on');
            $table->bigInteger('usg_created_by');
            $table->dateTime('usg_updated_on')->nullable();
            $table->bigInteger('usg_updated_by')->nullable();
            $table->dateTime('usg_deleted_on')->nullable();
            $table->bigInteger('usg_deleted_by')->nullable();
            $table->unique('usg_name', 'tbl_usg_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group');
    }
}
