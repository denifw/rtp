<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobNotificationReceiver extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_notification_receiver', function (Blueprint $table) {
            $table->bigIncrements('jnr_id');
            $table->bigInteger('jnr_jo_id')->unsigned();
            $table->foreign('jnr_jo_id', 'tbl_jnr_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jnr_cp_id')->unsigned();
            $table->foreign('jnr_cp_id', 'tbl_jnr_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jnr_created_by');
            $table->dateTime('jnr_created_on');
            $table->bigInteger('jnr_updated_by')->nullable();
            $table->dateTime('jnr_updated_on')->nullable();
            $table->bigInteger('jnr_deleted_by')->nullable();
            $table->dateTime('jnr_deleted_on')->nullable();
            $table->string('jnr_deleted_reason', 256)->nullable();
            $table->uuid('jnr_uid');
            $table->unique('jnr_uid', 'tbl_jnr_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_notification_receiver');
    }
}
