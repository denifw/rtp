<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_method', function (Blueprint $table) {
            $table->bigIncrements('pm_id');
            $table->bigInteger('pm_ss_id')->unsigned();
            $table->foreign('pm_ss_id', 'tbl_pm_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('pm_name', 125);
            $table->char('pm_active', 1)->default('Y');
            $table->bigInteger('pm_created_by');
            $table->dateTime('pm_created_on');
            $table->bigInteger('pm_updated_by')->nullable();
            $table->dateTime('pm_updated_on')->nullable();
            $table->bigInteger('pm_deleted_by')->nullable();
            $table->dateTime('pm_deleted_on')->nullable();
            $table->unique(['pm_name','pm_ss_id'], 'tbl_pm_ss_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_method');
    }
}
