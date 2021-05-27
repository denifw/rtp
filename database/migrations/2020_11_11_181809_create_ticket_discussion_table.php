<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketDiscussionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_discussion', function (Blueprint $table) {
            $table->bigIncrements('tcd_id');
            $table->bigInteger('tcd_tc_id')->unsigned();
            $table->foreign('tcd_tc_id', 'tbl_tcd_tc_id_foreign')->references('tc_id')->on('ticket');
            $table->string('tcd_discussion', 256);
            $table->bigInteger('tcd_created_by');
            $table->dateTime('tcd_created_on');
            $table->bigInteger('tcd_updated_by')->nullable();
            $table->dateTime('tcd_updated_on')->nullable();
            $table->bigInteger('tcd_deleted_by')->nullable();
            $table->dateTime('tcd_deleted_on')->nullable();
            $table->string('tcd_deleted_reason', 256)->nullable();
            $table->uuid('tcd_uid');
            $table->unique('tcd_uid', 'tbl_tcd_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_discussion');
    }
}
