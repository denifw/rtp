<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElectronicAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('electronic_account', function (Blueprint $table) {
            $table->bigIncrements('ea_id');
            $table->bigInteger('ea_ss_id')->unsigned();
            $table->foreign('ea_ss_id', 'tbl_ea_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('ea_code', 50);
            $table->string('ea_description', 256);
            $table->bigInteger('ea_cur_id')->unsigned();
            $table->foreign('ea_cur_id', 'tbl_ea_cur_id_fkey')->references('cur_id')->on('currency');
            $table->bigInteger('ea_us_id')->unsigned()->nullable();
            $table->foreign('ea_us_id', 'tbl_ea_us_id_fkey')->references('us_id')->on('users');
            $table->bigInteger('ea_block_by')->nullable();
            $table->foreign('ea_block_by', 'tbl_ea_block_by_foreign')->references('us_id')->on('users');
            $table->dateTime('ea_block_on')->nullable();
            $table->string('ea_block_reason', 256)->nullable();
            $table->bigInteger('ea_created_by');
            $table->dateTime('ea_created_on');
            $table->bigInteger('ea_updated_by')->nullable();
            $table->dateTime('ea_updated_on')->nullable();
            $table->bigInteger('ea_deleted_by')->nullable();
            $table->dateTime('ea_deleted_on')->nullable();
            $table->string('ea_deleted_reason', 256)->nullable();
            $table->uuid('ea_uid');
            $table->unique('ea_uid', 'tbl_ea_uid_unique');
            $table->unique(['ea_ss_id', 'ea_code'], 'tbl_ea_ss_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('electronic_account');
    }
}
