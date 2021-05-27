<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashAdvanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance', function (Blueprint $table) {
            $table->bigIncrements('ca_id');
            $table->string('ca_number', 125);
            $table->bigInteger('ca_cac_id')->unsigned();
            $table->foreign('ca_cac_id', 'tbl_ca_cac_id_foreign')->references('cac_id')->on('cash_account');
            $table->bigInteger('ca_jo_id')->unsigned();
            $table->foreign('ca_jo_id', 'tbl_ca_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('ca_cp_id')->unsigned();
            $table->foreign('ca_cp_id', 'tbl_ca_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->date('ca_date');
            $table->float('ca_amount');
            $table->bigInteger('ca_receive_by')->unsigned()->nullable();
            $table->dateTime('ca_receive_on')->nullable();
            $table->foreign('ca_receive_by', 'tbl_ca_receive_by_foreign')->references('us_id')->on('users');
            $table->float('ca_settlement')->nullable();
            $table->date('ca_return_date')->nullable();
            $table->float('ca_return_amount')->nullable();
            $table->bigInteger('ca_return_by')->unsigned()->nullable();
            $table->foreign('ca_return_by', 'tbl_ca_return_by_foreign')->references('us_id')->on('users');
            $table->dateTime('ca_return_on')->nullable();

            $table->bigInteger('ca_completed_by')->unsigned()->nullable();
            $table->foreign('ca_completed_by', 'tbl_ca_completed_by_foreign')->references('us_id')->on('users');
            $table->dateTime('ca_completed_on')->nullable();

            $table->bigInteger('ca_created_by');
            $table->dateTime('ca_created_on');
            $table->bigInteger('ca_updated_by')->nullable();
            $table->dateTime('ca_updated_on')->nullable();
            $table->string('ca_deleted_reason', 255)->nullable();
            $table->bigInteger('ca_deleted_by')->nullable();
            $table->dateTime('ca_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_advance');
    }
}
