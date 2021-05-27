<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation', function (Blueprint $table) {
            $table->bigIncrements('qt_id');
            $table->bigInteger('qt_ss_id')->unsigned();
            $table->foreign('qt_ss_id', 'tbl_qt_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('qt_number', 256)->nullable();
            $table->char('qt_type', 1)->default('Y');
            $table->bigInteger('qt_rel_id')->unsigned();
            $table->foreign('qt_rel_id', 'tbl_qt_rel_id_fkey')->references('rel_id')->on('relation');
            $table->bigInteger('qt_of_id')->unsigned();
            $table->foreign('qt_of_id', 'tbl_qt_of_id_fkey')->references('of_id')->on('office');
            $table->bigInteger('qt_cp_id')->unsigned()->nullable();
            $table->foreign('qt_cp_id', 'tbl_qt_cp_id_fkey')->references('cp_id')->on('contact_person');
            $table->bigInteger('qt_dl_id')->unsigned()->nullable();
            $table->foreign('qt_dl_id', 'tbl_qt_dl_id_fkey')->references('dl_id')->on('deal');
            $table->bigInteger('qt_order_of_id')->unsigned();
            $table->foreign('qt_order_of_id', 'tbl_qt_order_of_id_fkey')->references('of_id')->on('office');
            $table->bigInteger('qt_us_id')->unsigned();
            $table->foreign('qt_us_id', 'tbl_qt_us_id_fkey')->references('us_id')->on('users');
            $table->string('qt_commodity', 256)->nullable();
            $table->string('qt_requirement', 256)->nullable();
            $table->date('qt_start_date');
            $table->date('qt_end_date');
            $table->dateTime('qt_approve_on')->nullable();
            $table->bigInteger('qt_approve_by')->unsigned()->nullable();
            $table->foreign('qt_approve_by', 'tbl_qt_approve_by_fkey')->references('us_id')->on('users');
            $table->bigInteger('qt_created_by');
            $table->dateTime('qt_created_on');
            $table->bigInteger('qt_updated_by')->nullable();
            $table->dateTime('qt_updated_on')->nullable();
            $table->bigInteger('qt_deleted_by')->nullable();
            $table->dateTime('qt_deleted_on')->nullable();
            $table->string('qt_deleted_reason', 256)->nullable();
            $table->uuid('qt_uid');
            $table->unique('qt_uid', 'tbl_qt_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation');
    }
}
