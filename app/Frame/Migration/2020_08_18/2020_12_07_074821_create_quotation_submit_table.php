<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationSubmitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_submit', function (Blueprint $table) {
            $table->bigIncrements('qts_id');
            $table->bigInteger('qts_qt_id')->unsigned();
            $table->foreign('qts_qt_id', 'tbl_qts_qt_id_fkey')->references('qt_id')->on('quotation');
            $table->bigInteger('qts_created_by');
            $table->dateTime('qts_created_on');
            $table->bigInteger('qts_updated_by')->nullable();
            $table->dateTime('qts_updated_on')->nullable();
            $table->bigInteger('qts_deleted_by')->nullable();
            $table->dateTime('qts_deleted_on')->nullable();
            $table->string('qts_deleted_reason', 256)->nullable();
            $table->uuid('qts_uid');
            $table->unique('qts_uid', 'tbl_qts_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_submit');
    }
}
