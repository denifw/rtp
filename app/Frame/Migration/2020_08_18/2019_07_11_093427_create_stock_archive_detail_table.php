<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockArchiveDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_archive_detail', function (Blueprint $table) {
            $table->bigIncrements('sad_id');
            $table->bigInteger('sad_archive_ref')->unsigned();
            $table->bigInteger('sad_sa_id')->unsigned();
            $table->foreign('sad_sa_id', 'tbl_sad_sa_id_foreign')->references('sa_id')->on('stock_archive');
            $table->bigInteger('sad_gd_id')->unsigned();
            $table->foreign('sad_gd_id', 'tbl_sad_gd_id_foreign')->references('gd_id')->on('goods');
            $table->float('sad_inbound');
            $table->float('sad_outbound');
            $table->float('sad_adjustment');
            $table->float('sad_damage');
            $table->float('sad_good_stock');
            $table->float('sad_damage_stock');
            $table->bigInteger('sad_created_by');
            $table->dateTime('sad_created_on');
            $table->bigInteger('sad_updated_by')->nullable();
            $table->dateTime('sad_updated_on')->nullable();
            $table->bigInteger('sad_deleted_by')->nullable();
            $table->dateTime('sad_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_archive_detail');
    }
}
