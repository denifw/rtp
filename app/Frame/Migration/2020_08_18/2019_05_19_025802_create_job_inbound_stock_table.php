<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobInboundStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_inbound_stock', function (Blueprint $table) {
            $table->bigIncrements('jis_id');
            $table->bigInteger('jis_jid_id')->unsigned();
            $table->foreign('jis_jid_id', 'tbl_jis_jid_id_foreign')->references('jid_id')->on('job_inbound_detail');
            $table->float('jis_quantity');
            $table->bigInteger('jis_created_by');
            $table->dateTime('jis_created_on');
            $table->bigInteger('jis_updated_by')->nullable();
            $table->dateTime('jis_updated_on')->nullable();
            $table->bigInteger('jis_deleted_by')->nullable();
            $table->dateTime('jis_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_inbound_stock');
    }
}
