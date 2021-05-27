<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPrcAddAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price', function (Blueprint $table) {
            $table->dropForeign('tbl_prc_po_id_fkey');
            $table->dropColumn('prc_po_id');
            $table->bigInteger('prc_pol_id')->unsigned()->nullable();
            $table->foreign('prc_pol_id', 'tbl_prc_pol_id_fkey')->references('po_id')->on('port');
            $table->bigInteger('prc_pod_id')->unsigned()->nullable();
            $table->foreign('prc_pod_id', 'tbl_prc_pod_id_fkey')->references('po_id')->on('port');
            $table->string('prc_origin_address', 256)->nullable();
            $table->string('prc_destination_address', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
