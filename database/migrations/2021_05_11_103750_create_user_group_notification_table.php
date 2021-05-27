<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_notification', function (Blueprint $table) {
            $table->bigIncrements('ugn_id');
            $table->bigInteger('ugn_usg_id')->unsigned();
            $table->foreign('ugn_usg_id', 'tbl_ugn_usg_id_foreign')->references('usg_id')->on('user_group');
            $table->bigInteger('ugn_nt_id')->unsigned();
            $table->foreign('ugn_nt_id', 'tbl_ugn_nt_id_foreign')->references('nt_id')->on('notification_template');
            $table->dateTime('ugn_created_on');
            $table->bigInteger('ugn_created_by');
            $table->dateTime('ugn_updated_on')->nullable();
            $table->bigInteger('ugn_updated_by')->nullable();
            $table->dateTime('ugn_deleted_on')->nullable();
            $table->bigInteger('ugn_deleted_by')->nullable();
            $table->string('ugn_deleted_reason', 256)->nullable();
            $table->uuid('ugn_uid');
            $table->unique('ugn_uid', 'tbl_ugn_uid_unique');
            $table->unique(['ugn_usg_id', 'ugn_nt_id'], 'tbl_ugn_usg_id_nt_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_notification');
    }
}
