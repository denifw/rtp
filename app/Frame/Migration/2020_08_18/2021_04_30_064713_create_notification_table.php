<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->bigIncrements('nf_id');
            $table->bigInteger('nf_ss_id')->unsigned();
            $table->foreign('nf_ss_id', 'tbl_nf_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('nf_nt_id')->unsigned();
            $table->foreign('nf_nt_id', 'tbl_nf_nt_id_foreign')->references('nt_id')->on('notification_template');
            $table->string('nf_url', 256);
            $table->text('nf_url_key');
            $table->jsonb('nf_message_parameter');
            $table->bigInteger('nf_created_by');
            $table->dateTime('nf_created_on');
            $table->bigInteger('nf_updated_by')->nullable();
            $table->dateTime('nf_updated_on')->nullable();
            $table->bigInteger('nf_deleted_by')->nullable();
            $table->dateTime('nf_deleted_on')->nullable();
            $table->string('nf_deleted_reason', 256)->nullable();
            $table->uuid('nf_uid');
            $table->unique('nf_uid', 'tbl_nf_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification');
    }
}
