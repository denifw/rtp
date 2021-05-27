<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationReceiverTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_receiver', function (Blueprint $table) {
            $table->bigIncrements('nfr_id');
            $table->bigInteger('nfr_nf_id')->unsigned();
            $table->foreign('nfr_nf_id', 'tbl_nfr_nf_id_foreign')->references('nf_id')->on('notification');
            $table->bigInteger('nfr_us_id')->unsigned();
            $table->foreign('nfr_us_id', 'tbl_nfr_us_id_foreign')->references('us_id')->on('users');
            $table->char('nfr_delivered', 1)->default('N');
            $table->bigInteger('nfr_read_by')->nullable();
            $table->foreign('nfr_read_by', 'tbl_nfr_read_by_foreign')->references('us_id')->on('users');
            $table->dateTime('nfr_read_on')->nullable();
            $table->bigInteger('nfr_created_by');
            $table->dateTime('nfr_created_on');
            $table->bigInteger('nfr_updated_by')->nullable();
            $table->dateTime('nfr_updated_on')->nullable();
            $table->bigInteger('nfr_deleted_by')->nullable();
            $table->dateTime('nfr_deleted_on')->nullable();
            $table->string('nfr_deleted_reason', 256)->nullable();
            $table->uuid('nfr_uid');
            $table->unique('nfr_uid', 'tbl_nfr_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_receiver');
    }
}
