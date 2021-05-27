<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTermDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_term_document', function (Blueprint $table) {
            $table->bigIncrements('std_id');
            $table->bigInteger('std_ss_id')->unsigned();
            $table->foreign('std_ss_id', 'tbl_std_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('std_srt_id')->unsigned();
            $table->foreign('std_srt_id', 'tbl_std_srt_id_foreign')->references('srt_id')->on('service_term');
            $table->bigInteger('std_dct_id')->unsigned();
            $table->foreign('std_dct_id', 'tbl_std_dct_id_foreign')->references('dct_id')->on('document_type');
            $table->char('std_general', 1);
            $table->bigInteger('std_created_by');
            $table->dateTime('std_created_on');
            $table->bigInteger('std_updated_by')->nullable();
            $table->dateTime('std_updated_on')->nullable();
            $table->bigInteger('std_deleted_by')->nullable();
            $table->dateTime('std_deleted_on')->nullable();
            $table->unique(['std_ss_id', 'std_srt_id', 'std_dct_id'], 'tbl_std_ss_ac_dct_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_term_document');
    }
}
