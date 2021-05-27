<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElectronicBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('electronic_balance', function (Blueprint $table) {
            $table->bigIncrements('eb_id');
            $table->bigInteger('eb_ea_id')->unsigned();
            $table->foreign('eb_ea_id', 'tbl_eb_ea_id_fkey')->references('ea_id')->on('electronic_account');
            $table->float('eb_amount');
            $table->bigInteger('eb_created_by');
            $table->dateTime('eb_created_on');
            $table->bigInteger('eb_updated_by')->nullable();
            $table->dateTime('eb_updated_on')->nullable();
            $table->bigInteger('eb_deleted_by')->nullable();
            $table->dateTime('eb_deleted_on')->nullable();
            $table->string('eb_deleted_reason', 256)->nullable();
            $table->uuid('eb_uid');
            $table->unique('eb_uid', 'tbl_eb_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('electronic_balance');
    }
}
