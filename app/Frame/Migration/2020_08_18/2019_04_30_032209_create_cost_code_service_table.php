<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostCodeServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_code_service', function (Blueprint $table) {
            $table->bigIncrements('ccs_id');
            $table->bigInteger('ccs_cc_id')->unsigned();
            $table->foreign('ccs_cc_id', 'tbl_ccs_cc_id_foreign')->references('cc_id')->on('cost_code');
            $table->bigInteger('ccs_srv_id')->unsigned();
            $table->foreign('ccs_srv_id', 'tbl_ccs_srv_id_foreign')->references('srv_id')->on('service');
            $table->char('ccs_active', 1)->default('Y');
            $table->bigInteger('ccs_created_by');
            $table->dateTime('ccs_created_on');
            $table->bigInteger('ccs_updated_by')->nullable();
            $table->dateTime('ccs_updated_on')->nullable();
            $table->bigInteger('ccs_deleted_by')->nullable();
            $table->dateTime('ccs_deleted_on')->nullable();
            $table->unique(['ccs_cc_id', 'ccs_srv_id'], 'tbl_ccs_cc_id_srv_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_code_service');
    }
}
