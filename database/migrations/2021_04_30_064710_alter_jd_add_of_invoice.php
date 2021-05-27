<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJdAddOfInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_deposit', function (Blueprint $table) {
            $table->bigInteger('jd_invoice_of_id')->unsigned();
            $table->foreign('jd_invoice_of_id', 'tbl_jd_invoice_of_id_fkey')->references('of_id')->on('office');
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
