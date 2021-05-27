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
            $table->bigIncrements('uga_id');
            $table->bigInteger('uga_usg_id')->unsigned();
            $table->foreign('uga_usg_id', 'tbl_uga_usg_id_foreign')->references('usg_id')->on('user_group');
            $table->bigInteger('uga_aa_id')->unsigned();
            $table->foreign('uga_aa_id', 'tbl_uga_aa_id_foreign')->references('aa_id')->on('api_access');
            $table->dateTime('uga_created_on');
            $table->bigInteger('uga_created_by');
            $table->dateTime('uga_updated_on')->nullable();
            $table->bigInteger('uga_updated_by')->nullable();
            $table->dateTime('uga_deleted_on')->nullable();
            $table->bigInteger('uga_deleted_by')->nullable();
            $table->unique(['uga_usg_id', 'uga_aa_id'], 'tbl_uga_usg_id_aa_id_unique');
        });
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
