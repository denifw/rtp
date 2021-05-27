<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEqAddDocDriver extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->bigInteger('eq_doc_id')->unsigned()->nullable();
            $table->foreign('eq_doc_id','tbl_eq_doc_id_fkey')->references('doc_id')->on('document');
            $table->bigInteger('eq_driver_id')->unsigned()->nullable();
            $table->foreign('eq_driver_id','tbl_eq_driver_id_fkey')->references('cp_id')->on('contact_person');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
