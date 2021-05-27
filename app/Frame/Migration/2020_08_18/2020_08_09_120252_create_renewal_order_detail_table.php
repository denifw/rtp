<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewal_order_detail', function (Blueprint $table) {
            $table->bigIncrements('rnd_id');
            $table->bigInteger('rnd_rno_id')->unsigned();
            $table->foreign('rnd_rno_id', 'tbl_rnd_rno_id_foreign')->references('rno_id')->on('renewal_order');
            $table->bigInteger('rnd_rnt_id')->unsigned();
            $table->foreign('rnd_rnt_id', 'tbl_rnd_rnt_id_foreign')->references('rnt_id')->on('renewal_type');
            $table->float('rnd_est_cost');
            $table->string('rnd_remark', 255)->nullable();
            $table->bigInteger('rnd_created_by');
            $table->dateTime('rnd_created_on');
            $table->bigInteger('rnd_updated_by')->nullable();
            $table->dateTime('rnd_updated_on')->nullable();
            $table->bigInteger('rnd_deleted_by')->nullable();
            $table->dateTime('rnd_deleted_on')->nullable();
            $table->unique(['rnd_rno_id', 'rnd_rnt_id'], 'tbl_rnd_rno_id_rnt_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewal_order_detail');
    }
}
