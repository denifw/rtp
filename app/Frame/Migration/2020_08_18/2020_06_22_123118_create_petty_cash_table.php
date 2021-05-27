<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePettyCashTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->bigIncrements('pc_id');
            $table->bigInteger('pc_ss_id')->unsigned();
            $table->foreign('pc_ss_id', 'tbl_pc_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('pc_cac_id')->unsigned();
            $table->foreign('pc_cac_id', 'tbl_pc_cac_id_foreign')->references('cac_id')->on('cash_account');
            $table->date('pc_date');
            $table->float('pc_amount');
            $table->string('pc_notes', 255)->nullable();
            $table->bigInteger('pc_approve_by')->nullable();
            $table->dateTime('pc_approve_on')->nullable();
            $table->bigInteger('pc_paid_by')->nullable();
            $table->string('pc_paid_ref', 255)->nullable();
            $table->dateTime('pc_paid_on')->nullable();
            $table->bigInteger('pc_created_by');
            $table->dateTime('pc_created_on');
            $table->bigInteger('pc_updated_by')->nullable();
            $table->dateTime('pc_updated_on')->nullable();
            $table->bigInteger('pc_deleted_by')->nullable();
            $table->dateTime('pc_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('petty_cash');
    }
}
