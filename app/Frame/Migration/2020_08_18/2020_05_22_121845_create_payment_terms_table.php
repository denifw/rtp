<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->bigIncrements('pt_id');
            $table->bigInteger('pt_ss_id')->unsigned();
            $table->foreign('pt_ss_id', 'tbl_pt_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('pt_name', 125);
            $table->float('pt_days');
            $table->char('pt_active', 1)->default('Y');
            $table->bigInteger('pt_created_by');
            $table->dateTime('pt_created_on');
            $table->bigInteger('pt_updated_by')->nullable();
            $table->dateTime('pt_updated_on')->nullable();
            $table->bigInteger('pt_deleted_by')->nullable();
            $table->dateTime('pt_deleted_on')->nullable();
            $table->unique(['pt_ss_id', 'pt_name'], 'tbl_pt_ss_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_terms');
    }
}
