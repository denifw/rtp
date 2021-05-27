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
            $table->increments('nf_id');
            $table->bigInteger('nf_ss_id')->unsigned();
            $table->foreign('nf_ss_id', 'tbl_nf_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('nf_pn_id')->unsigned();
            $table->foreign('nf_pn_id', 'tbl_nf_pn_id_foreign')->references('pn_id')->on('page_notification');
            $table->json('nf_url_param');
            $table->json('nf_message_param');
            $table->char('nf_active', 1)->default('Y');
            $table->bigInteger('nf_created_by');
            $table->dateTime('nf_created_on');
            $table->bigInteger('nf_updated_by')->nullable();
            $table->dateTime('nf_updated_on')->nullable();
            $table->bigInteger('nf_deleted_by')->nullable();
            $table->dateTime('nf_deleted_on')->nullable();
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
