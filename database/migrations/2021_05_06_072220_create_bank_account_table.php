<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_account', function (Blueprint $table) {
            $table->bigIncrements('ba_id');
            $table->bigInteger('ba_ss_id')->unsigned();
            $table->foreign('ba_ss_id', 'tbl_ba_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('ba_code', 50);
            $table->string('ba_description', 256);
            $table->bigInteger('ba_bn_id')->unsigned();
            $table->foreign('ba_bn_id', 'tbl_ba_bn_id_fkey')->references('bn_id')->on('bank');
            $table->bigInteger('ba_cur_id')->unsigned();
            $table->foreign('ba_cur_id', 'tbl_ba_cur_id_fkey')->references('cur_id')->on('currency');
            $table->string('ba_account_number', 256);
            $table->string('ba_account_name', 256);
            $table->string('ba_bank_branch', 256)->nullable();
            $table->char('ba_main', 1)->default('N');
            $table->char('ba_receivable', 1)->default('N');
            $table->char('ba_payable', 1)->default('N');
            $table->bigInteger('ba_us_id')->unsigned()->nullable();
            $table->foreign('ba_us_id', 'tbl_ba_us_id_fkey')->references('us_id')->on('users');
            $table->float('ba_limit');
            $table->bigInteger('ba_block_by')->nullable();
            $table->foreign('ba_block_by', 'tbl_ba_block_by_foreign')->references('us_id')->on('users');
            $table->dateTime('ba_block_on')->nullable();
            $table->string('ba_block_reason', 256)->nullable();
            $table->bigInteger('ba_created_by');
            $table->dateTime('ba_created_on');
            $table->bigInteger('ba_updated_by')->nullable();
            $table->dateTime('ba_updated_on')->nullable();
            $table->bigInteger('ba_deleted_by')->nullable();
            $table->dateTime('ba_deleted_on')->nullable();
            $table->string('ba_deleted_reason', 256)->nullable();
            $table->uuid('ba_uid');
            $table->unique('ba_uid', 'tbl_ba_uid_unique');
            $table->unique(['ba_ss_id', 'ba_code'], 'tbl_ba_ss_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_account');
    }
}
