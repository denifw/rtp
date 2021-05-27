<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPrcTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price', function (Blueprint $table) {
            $table->dropForeign('tbl_prc_po_origin_foreign');
            $table->dropForeign('tbl_prc_po_destination_foreign');
            $table->dropColumn('prc_number');
            $table->dropColumn('prc_po_origin');
            $table->dropColumn('prc_po_destination');
            $table->bigInteger('prc_po_id')->unsigned()->nullable();
            $table->foreign('prc_po_id', 'tbl_prc_po_id_fkey')->references('po_id')->on('port');
        });
        Schema::table('price_detail', function (Blueprint $table) {
            $table->string('prd_description', 256);
            $table->float('prd_total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price', function (Blueprint $table) {
            $table->string('prc_number', 256)->nullable();
            $table->bigInteger('prc_po_origin')->unsigned()->nullable();
            $table->foreign('prc_po_origin', 'tbl_prc_po_origin_foreign')->references('po_id')->on('port');
            $table->bigInteger('prc_po_destination')->unsigned()->nullable();
            $table->foreign('prc_po_destination', 'tbl_prc_po_destination_foreign')->references('po_id')->on('port');
        });
        Schema::table('price_detail', function (Blueprint $table) {
            $table->dropColumn('prd_description');
            $table->dropColumn('prd_total');
        });
    }
}
