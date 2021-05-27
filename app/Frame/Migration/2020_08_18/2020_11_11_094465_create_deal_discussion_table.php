<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealDiscussionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deal_discussion', function (Blueprint $table) {
            $table->bigIncrements('dld_id');
            $table->bigInteger('dld_dl_id')->unsigned();
            $table->foreign('dld_dl_id', 'tbl_dld_dl_id_foreign')->references('dl_id')->on('deal');
            $table->string('dld_discussion', 256);
            $table->bigInteger('dld_created_by');
            $table->dateTime('dld_created_on');
            $table->bigInteger('dld_updated_by')->nullable();
            $table->dateTime('dld_updated_on')->nullable();
            $table->bigInteger('dld_deleted_by')->nullable();
            $table->dateTime('dld_deleted_on')->nullable();
            $table->string('dld_deleted_reason', 256)->nullable();
            $table->uuid('dld_uid');
            $table->unique('dld_uid', 'tbl_dld_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deal_discussion');
    }
}
