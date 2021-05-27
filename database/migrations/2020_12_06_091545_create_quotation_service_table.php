<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_service', function (Blueprint $table) {
            $table->bigIncrements('qs_id');
            $table->bigInteger('qs_qt_id')->unsigned();
            $table->foreign('qs_qt_id', 'tbl_qs_qt_id_fkey')->references('qt_id')->on('quotation');
            $table->bigInteger('qs_srv_id')->unsigned();
            $table->foreign('qs_srv_id', 'tbl_qs_srv_id_fkey')->references('srv_id')->on('service');
            $table->bigInteger('qs_created_by');
            $table->dateTime('qs_created_on');
            $table->bigInteger('qs_updated_by')->nullable();
            $table->dateTime('qs_updated_on')->nullable();
            $table->bigInteger('qs_deleted_by')->nullable();
            $table->dateTime('qs_deleted_on')->nullable();
            $table->string('qs_deleted_reason', 256)->nullable();
            $table->uuid('qs_uid');
            $table->unique('qs_uid', 'tbl_qs_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_service');
    }
}
