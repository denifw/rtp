<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobInklaringImporoveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_inklaring', function (Blueprint $table) {
            $table->bigInteger('jik_so_id')->unsigned()->nullable();
            $table->foreign('jik_so_id', 'tbl_jik_so_id_fkey')->references('so_id')->on('sales_order');
            $table->date('jik_closing_date')->nullable();
            $table->time('jik_closing_time')->nullable();
            $table->renameColumn('jik_complete_gate_in_on', 'jik_gate_pass_on');
            $table->renameColumn('jik_complete_gate_in_by', 'jik_gate_pass_by');
            # Drop Foreign
            $table->dropForeign('tbl_jik_wh_id_foreign');
            $table->dropForeign('tbl_jik_cct_id_foreign');
            $table->dropForeign('tbl_jik_cdt_id_foreign');
            $table->dropForeign('tbl_jik_consignee_id_foreign');
            $table->dropForeign('tbl_jik_of_consignee_id_foreign');
            $table->dropForeign('tbl_jik_pic_consignee_id_foreign');
            $table->dropForeign('tbl_jik_shipper_id_foreign');
            $table->dropForeign('tbl_jik_of_shipper_id_foreign');
            $table->dropForeign('tbl_jik_pic_shipper_id_foreign');
            $table->dropForeign('tbl_jik_notify_id_foreign');
            $table->dropForeign('tbl_jik_of_notify_id_foreign');
            $table->dropForeign('tbl_jik_pic_notify_id_foreign');
            $table->dropForeign('tbl_jik_pol_id_foreign');
            $table->dropForeign('tbl_jik_pod_id_foreign');
            $table->dropForeign('tbl_jik_tm_id_foreign');
            # Drop Column
            $table->dropColumn('jik_wh_id');
            $table->dropColumn('jik_cct_id');
            $table->dropColumn('jik_cdt_id');
            $table->dropColumn('jik_planning_date');
            $table->dropColumn('jik_consignee_id');
            $table->dropColumn('jik_of_consignee_id');
            $table->dropColumn('jik_pic_consignee_id');
            $table->dropColumn('jik_shipper_id');
            $table->dropColumn('jik_of_shipper_id');
            $table->dropColumn('jik_pic_shipper_id');
            $table->dropColumn('jik_notify_id');
            $table->dropColumn('jik_of_notify_id');
            $table->dropColumn('jik_pic_notify_id');
            $table->dropColumn('jik_pol_id');
            $table->dropColumn('jik_pod_id');
            $table->dropColumn('jik_tm_id');
            $table->dropColumn('jik_transport_name');
            $table->dropColumn('jik_voyage_number');
            $table->dropColumn('jik_sppd_ref');
            $table->dropColumn('jik_sppd_date');
            $table->dropColumn('jik_do_ref');
            $table->dropColumn('jik_do_expired');
            $table->dropColumn('jik_eta_date');
            $table->dropColumn('jik_eta_time');
            $table->dropColumn('jik_ata_date');
            $table->dropColumn('jik_ata_time');
            $table->dropColumn('jik_manifest_ref');
            $table->dropColumn('jik_manifest_date');
            $table->dropColumn('jik_manifest_pos');
            $table->dropColumn('jik_manifest_sub_pos');
            $table->dropColumn('jik_gate_in_on');
            $table->dropColumn('jik_gate_in_by');

        });
        $query = 'SELECT jik.jik_id, jo.jo_id, jo.jo_so_id, jo.jo_ss_id
                    FROM job_inklaring as jik
                    INNER JOIN job_order as jo ON jik.jik_jo_id = jo.jo_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_inklaring')
                ->where('jik_id', $row->jik_id)
                ->update([
                    'jik_so_id' => $row->jo_so_id,
                ]);
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
