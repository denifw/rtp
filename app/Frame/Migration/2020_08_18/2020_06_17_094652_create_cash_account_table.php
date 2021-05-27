<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_account', function (Blueprint $table) {
            $table->bigIncrements('cac_id');
            $table->bigInteger('cac_ss_id')->unsigned();
            $table->foreign('cac_ss_id', 'tbl_cac_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('cac_code', 50);
            $table->bigInteger('cac_srv_id')->unsigned();
            $table->foreign('cac_srv_id', 'tbl_cac_srv_id_foreign')->references('srv_id')->on('service');
            $table->bigInteger('cac_us_id')->unsigned();
            $table->foreign('cac_us_id', 'tbl_cac_us_id_foreign')->references('us_id')->on('users');
            $table->float('cac_limit');
            $table->char('cac_active', 1);
            $table->bigInteger('cac_created_by');
            $table->dateTime('cac_created_on');
            $table->bigInteger('cac_updated_by')->nullable();
            $table->dateTime('cac_updated_on')->nullable();
            $table->bigInteger('cac_deleted_by')->nullable();
            $table->dateTime('cac_deleted_on')->nullable();
            $table->unique(['cac_ss_id', 'cac_code'], 'tbl_cac_ss_code_unique');
            $table->unique(['cac_ss_id', 'cac_srv_id', 'cac_us_id'], 'tbl_cac_ss_srv_us_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_account');
    }
}
