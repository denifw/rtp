<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobTruckingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_trucking_detail', function (Blueprint $table) {
            $table->bigIncrements('jtd_id');
            $table->bigInteger('jtd_jt_id')->unsigned();
            $table->foreign('jtd_jt_id', 'tbl_jtd_jt_id_foreign')->references('jt_id')->on('job_trucking');

            $table->bigInteger('jtd_rel_id')->unsigned()->nullable();
            $table->foreign('jtd_rel_id', 'tbl_jtd_rel_id_foreign')->references('rel_id')->on('relation');

            $table->bigInteger('jtd_of_id')->unsigned()->nullable();
            $table->foreign('jtd_of_id', 'tbl_jtd_of_id_foreign')->references('of_id')->on('office');

            $table->bigInteger('jtd_pic_id')->unsigned()->nullable();
            $table->foreign('jtd_pic_id', 'tbl_jtd_pic_id_foreign')->references('cp_id')->on('contact_person');

            $table->bigInteger('jtd_jog_id')->unsigned();
            $table->foreign('jtd_jog_id', 'tbl_jtd_jog_id_foreign')->references('jog_id')->on('job_goods');

            $table->string('jtd_reference', 255)->nullable();
            $table->float('jtd_quantity')->nullable();
            $table->float('jtd_qty_good')->nullable();
            $table->float('jtd_qty_damage')->nullable();

            $table->integer('jtd_order')->nullable();
            $table->char('jtd_type', 1);

            $table->date('jtd_eta_date')->nullable();
            $table->time('jtd_eta_time')->nullable();
            $table->dateTime('jtd_ata_on')->nullable();
            $table->dateTime('jtd_start_on')->nullable();
            $table->dateTime('jtd_end_on')->nullable();
            $table->dateTime('jtd_atd_on')->nullable();

            $table->bigInteger('jtd_created_by');
            $table->dateTime('jtd_created_on');
            $table->bigInteger('jtd_updated_by')->nullable();
            $table->dateTime('jtd_updated_on')->nullable();
            $table->bigInteger('jtd_deleted_by')->nullable();
            $table->dateTime('jtd_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_trucking_detail');
    }
}
