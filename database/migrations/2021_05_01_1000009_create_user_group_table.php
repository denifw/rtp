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
            $table->uuid('usg_id')->primary();
            $table->string('usg_name', 128);
            $table->char('usg_active', 1)->default('Y');
            $table->uuid('usg_created_by');
            $table->dateTime('usg_created_on');
            $table->uuid('usg_updated_by')->nullable();
            $table->dateTime('usg_updated_on')->nullable();
            $table->uuid('usg_deleted_by')->nullable();
            $table->dateTime('usg_deleted_on')->nullable();
            $table->string('usg_deleted_reason', 256)->nullable();
            $table->unique('usg_name', 'tbl_usg_name_unique');
        });
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