<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJirAddPackingNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_inbound_receive', function (Blueprint $table) {
            $table->string('jir_packing_number', 255)->nullable();
            $table->date('jir_expired_date')->nullable();
        });
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->date('jid_expired_date')->nullable();
        });
        $query = 'SELECT jo.jo_id, jo.jo_number, jo.jo_start_on, ji.ji_id, jir.jir_id, jid.jid_id
                    FROM job_order as jo
                             INNER JOIN job_inbound as ji ON ji.ji_jo_id = jo.jo_id
                             INNER JOIN job_inbound_receive as jir ON jir.jir_ji_id = ji.ji_id
                             LEFT OUTER JOIN job_inbound_detail as jid on jir.jir_id = jid.jid_jir_id
                    WHERE jo.jo_ss_id = 6
                    ORDER BY jo.jo_id, jir.jir_id, jid.jid_id';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        $jirIds = [];
        foreach ($sqlResults as $row) {
            $lot = mb_substr($row->jo_start_on, 0, 10);
            if (in_array($row->jir_id, $jirIds, true) === false) {
                DB::table('job_inbound_receive')
                    ->where('jir_id', $row->jir_id)
                    ->update([
                        'jir_lot_number' => $lot,
                        'jir_packing_number' => $row->jo_number,
                    ]);
                $jirIds[] = $row->jir_id;
            }
            if(empty($row->jid_id) === false) {
                DB::table('job_inbound_detail')
                    ->where('jid_id', $row->jid_id)
                    ->update([
                        'jid_lot_number' => $lot,
                        'jid_packing_number' => $row->jo_number,
                    ]);
            }

        }

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
