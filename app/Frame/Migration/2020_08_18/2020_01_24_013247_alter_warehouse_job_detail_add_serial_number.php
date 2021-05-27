<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterWarehouseJobDetailAddSerialNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->string('jid_serial_number', 255)->nullable();
            $table->string('jid_packing_number', 255)->nullable();
        });
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->string('jod_serial_number', 255)->nullable();
            $table->integer('jod_packing_number')->nullable();
            $table->bigInteger('jod_jid_id')->unsigned()->nullable()->change();
            $table->bigInteger('jod_whs_id')->unsigned()->nullable();
            $table->foreign('jod_whs_id', 'tbl_jod_whs_id_foreign')->references('whs_id')->on('warehouse_storage');
        });
        $query = 'SELECT jod.jod_id, jid.jid_id, jid.jid_whs_id
                FROM job_outbound_detail as jod INNER JOIN
                job_inbound_detail as jid ON jid.jid_id = jod.jod_jid_id ';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_outbound_detail')
                ->where('jod_id', $row->jod_id)
                ->update(['jod_whs_id' => $row->jid_whs_id]);
        }
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->bigInteger('jod_whs_id')->unsigned()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->dropColumn('jid_serial_number');
            $table->dropColumn('jid_packing_number');
        });
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jod_whs_id_foreign');
            $table->dropColumn('jod_serial_number');
            $table->dropColumn('jod_packing_number');
            $table->dropColumn('jod_whs_id');
        });
    }
}
