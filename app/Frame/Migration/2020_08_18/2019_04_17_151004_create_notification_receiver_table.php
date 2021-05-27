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
            $table->increments('nfr_id');
            $table->bigInteger('nfr_nf_id')->unsigned();
            $table->foreign('nfr_nf_id', 'tbl_nfr_nf_id_foreign')->references('nf_id')->on('notification');
            $table->bigInteger('nfr_receiver')->unsigned();
            $table->foreign('nfr_receiver', 'tbl_nfr_receiver_foreign')->references('us_id')->on('users');
            $table->char('nfr_delivered', 1)->default('N');
            $table->char('nfr_read', 1)->default('N');
            $table->char('nfr_active', 1)->default('Y');
            $table->bigInteger('nfr_created_by');
            $table->dateTime('nfr_created_on');
            $table->bigInteger('nfr_updated_by')->nullable();
            $table->dateTime('nfr_updated_on')->nullable();
            $table->bigInteger('nfr_deleted_by')->nullable();
            $table->dateTime('nfr_deleted_on')->nullable();
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
