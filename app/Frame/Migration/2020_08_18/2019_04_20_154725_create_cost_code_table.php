<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_code', function (Blueprint $table) {
            $table->bigIncrements('cc_id');
            $table->bigInteger('cc_ss_id')->unsigned();
            $table->foreign('cc_ss_id', 'tbl_cc_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('cc_code', 50);
            $table->string('cc_name', 150);
            $table->bigInteger('cc_parent')->nullable();
            $table->char('cc_type', 1)->default('S');
            $table->char('cc_active', 1)->default('Y');
            $table->bigInteger('cc_created_by');
            $table->dateTime('cc_created_on');
            $table->bigInteger('cc_updated_by')->nullable();
            $table->dateTime('cc_updated_on')->nullable();
            $table->bigInteger('cc_deleted_by')->nullable();
            $table->dateTime('cc_deleted_on')->nullable();
            $table->unique(['cc_ss_id', 'cc_code'], 'tbl_cc_ss_id_code_unique');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_code');
    }
}
