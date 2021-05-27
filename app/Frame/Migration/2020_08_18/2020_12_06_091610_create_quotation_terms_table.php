<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_terms', function (Blueprint $table) {
            $table->bigIncrements('qtm_id');
            $table->bigInteger('qtm_qt_id')->unsigned();
            $table->foreign('qtm_qt_id', 'tbl_qtm_qt_id_fkey')->references('qt_id')->on('quotation');
            $table->json('qtm_terms');
            $table->bigInteger('qtm_created_by');
            $table->dateTime('qtm_created_on');
            $table->bigInteger('qtm_updated_by')->nullable();
            $table->dateTime('qtm_updated_on')->nullable();
            $table->bigInteger('qtm_deleted_by')->nullable();
            $table->dateTime('qtm_deleted_on')->nullable();
            $table->string('qtm_deleted_reason', 256)->nullable();
            $table->uuid('qtm_uid');
            $table->unique('qtm_uid', 'tbl_qtm_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_terms');
    }
}
