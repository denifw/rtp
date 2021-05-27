<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order', function (Blueprint $table) {
            # Drop existing columns
            $table->dropForeign('tbl_so_relation_id_foreign');
            $table->dropForeign('tbl_so_pic_relation_foreign');
            $table->dropColumn('so_type');
            $table->dropColumn('so_relation_id');
            $table->dropColumn('so_pic_relation');
            $table->dropColumn('so_planning_date');
            $table->dropColumn('so_planning_time');
            $table->dropColumn('so_party');
            # Add New Column
            $table->char('so_consolidate', 1)->nullable();
            $table->bigInteger('so_sales_id')->unsigned()->nullable();
            $table->foreign('so_sales_id', 'tbl_so_sales_id_foreign')->references('cp_id')->on('contact_person');

            $table->char('so_inklaring', 1)->nullable();
            $table->char('so_delivery', 1)->nullable();
            $table->char('so_multi_load', 1)->nullable();
            $table->char('so_multi_unload', 1)->nullable();
            $table->char('so_warehouse', 1)->nullable();
            $table->bigInteger('so_ict_id')->unsigned()->nullable();
            $table->foreign('so_ict_id', 'tbl_so_ict_id_foreign')->references('ict_id')->on('inco_terms');

            $table->bigInteger('so_cct_id')->unsigned()->nullable();
            $table->foreign('so_cct_id', 'tbl_so_cct_id_foreign')->references('cct_id')->on('customs_clearance_type');
            $table->bigInteger('so_cdt_id')->unsigned()->nullable();
            $table->foreign('so_cdt_id', 'tbl_so_cdt_id_foreign')->references('cdt_id')->on('customs_document_type');
            $table->bigInteger('so_pol_id')->unsigned()->nullable();
            $table->foreign('so_pol_id', 'tbl_so_pol_id_foreign')->references('po_id')->on('port');
            $table->date('so_departure_date')->nullable();
            $table->time('so_departure_time')->nullable();
            $table->date('so_atd_date')->nullable();
            $table->time('so_atd_time')->nullable();
            $table->bigInteger('so_pod_id')->unsigned()->nullable();
            $table->foreign('so_pod_id', 'tbl_so_pod_id_foreign')->references('po_id')->on('port');
            $table->date('so_arrival_date')->nullable();
            $table->time('so_arrival_time')->nullable();
            $table->date('so_ata_date')->nullable();
            $table->time('so_ata_time')->nullable();
            $table->bigInteger('so_tm_id')->unsigned()->nullable();
            $table->foreign('so_tm_id', 'tbl_so_tm_id_foreign')->references('tm_id')->on('transport_module');
            $table->bigInteger('so_consignee_id')->unsigned()->nullable();
            $table->foreign('so_consignee_id', 'tbl_so_consignee_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('so_consignee_of_id')->unsigned()->nullable();
            $table->foreign('so_consignee_of_id', 'tbl_so_consignee_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('so_consignee_cp_id')->unsigned()->nullable();
            $table->foreign('so_consignee_cp_id', 'tbl_so_consignee_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('so_shipper_id')->unsigned()->nullable();
            $table->foreign('so_shipper_id', 'tbl_so_shipper_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('so_shipper_of_id')->unsigned()->nullable();
            $table->foreign('so_shipper_of_id', 'tbl_so_shipper_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('so_shipper_cp_id')->unsigned()->nullable();
            $table->foreign('so_shipper_cp_id', 'tbl_so_shipper_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('so_notify_id')->unsigned()->nullable();
            $table->foreign('so_notify_id', 'tbl_so_notify_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('so_notify_of_id')->unsigned()->nullable();
            $table->foreign('so_notify_of_id', 'tbl_so_notify_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('so_notify_cp_id')->unsigned()->nullable();
            $table->foreign('so_notify_cp_id', 'tbl_so_notify_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('so_carrier_id')->unsigned()->nullable();
            $table->foreign('so_carrier_id', 'tbl_so_carrier_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('so_carrier_of_id')->unsigned()->nullable();
            $table->foreign('so_carrier_of_id', 'tbl_so_carrier_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('so_carrier_cp_id')->unsigned()->nullable();
            $table->foreign('so_carrier_cp_id', 'tbl_so_carrier_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('so_eq_id')->unsigned()->nullable();
            $table->foreign('so_eq_id', 'tbl_so_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->string('so_transport_number', 128)->nullable();
            $table->string('so_old_transport', 256)->nullable();
            $table->string('so_sppd_ref', 128)->nullable();
            $table->date('so_sppd_date')->nullable();
            $table->string('so_do_ref', 128)->nullable();
            $table->date('so_do_expired')->nullable();
            $table->string('so_manifest_ref', 128)->nullable();
            $table->date('so_manifest_date')->nullable();
            $table->string('so_manifest_pos', 128)->nullable();
            $table->string('so_manifest_sub_pos', 128)->nullable();
            $table->string('so_plb', 1)->nullable();
            $table->bigInteger('so_wh_id')->unsigned()->nullable();
            $table->foreign('so_wh_id', 'tbl_so_wh_id_fkey')->references('wh_id')->on('warehouse');

            $table->bigInteger('so_dp_id')->unsigned()->nullable();
            $table->foreign('so_dp_id', 'tbl_so_dp_id_foreign')->references('of_id')->on('office');
            $table->date('so_pick_date')->nullable();
            $table->time('so_pick_time')->nullable();

            $table->bigInteger('so_dr_id')->unsigned()->nullable();
            $table->foreign('so_dr_id', 'tbl_so_dr_id_foreign')->references('of_id')->on('office');
            $table->date('so_return_date')->nullable();
            $table->time('so_return_time')->nullable();
        });

        $sqlJik = 'SELECT jik.jik_id, jo.jo_id, jo.jo_so_id, jo.jo_ss_id, jik.jik_cdt_id, jik.jik_cct_id,
                           jik.jik_pol_id, jik.jik_pod_id, jik.jik_eta_date, jik.jik_eta_time, jik.jik_ata_date, jik.jik_ata_time,
                           srt.srt_route, jik.jik_tm_id, jik.jik_consignee_id, jik.jik_of_consignee_id, jik.jik_shipper_id, jik.jik_of_shipper_id,
                           jik.jik_notify_id, jik.jik_of_notify_id, jik.jik_transport_name, jik.jik_voyage_number,
                           jik.jik_sppd_ref, jik.jik_sppd_date, jik.jik_do_ref, jik.jik_do_expired, jik.jik_manifest_ref,
                           jik.jik_manifest_date, jik.jik_manifest_pos, jik.jik_manifest_sub_pos,
                            jik.jik_pic_consignee_id, jik.jik_pic_shipper_id, jik.jik_pic_notify_id
                    FROM job_inklaring as jik
                             INNER JOIN job_order as jo ON jik.jik_jo_id = jo.jo_id
                             INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                             LEFT OUTER JOIN warehouse as wh ON jik.jik_wh_id = wh.wh_id
                             LEFT OUTER JOIN office as oo ON wh.wh_of_id = oo.of_id
                    WHERE jo.jo_deleted_on IS NULL and jo.jo_id <> 7367 and jo.jo_so_id IS NOT NULL';
        $jikResults = DB::select($sqlJik);
        foreach ($jikResults as $row) {
            $plb = 'N';
            $ssId = (int)$row->jo_ss_id;
            $cdtId = (int)$row->jik_cdt_id;
            if ($ssId === 3 && ($cdtId === 5 || $cdtId === 15)) {
                $plb = 'Y';
            }
            $ictId = 2;
            $route = $row->srt_route;
            if ($route === 'jii' || $route === 'jiic') {
                $ictId = 3;
            }
            DB::table('sales_order')->where('so_id', $row->jo_so_id)
                ->update([
                    'so_ict_id' => $ictId,
                    'so_plb' => $plb,
                    'so_inklaring' => 'Y',
                    'so_cct_id' => $row->jik_cct_id,
                    'so_cdt_id' => $row->jik_cdt_id,
                    'so_pol_id' => $row->jik_pol_id,
                    'so_departure_date' => $row->jik_eta_date,
                    'so_departure_time' => $row->jik_eta_time,
                    'so_pod_id' => $row->jik_pod_id,
                    'so_arrival_date' => $row->jik_ata_date,
                    'so_arrival_time' => $row->jik_ata_time,
                    'so_tm_id' => $row->jik_tm_id,
                    'so_consignee_id' => $row->jik_consignee_id,
                    'so_consignee_of_id' => $row->jik_of_consignee_id,
                    'so_consignee_cp_id' => $row->jik_pic_consignee_id,
                    'so_shipper_id' => $row->jik_shipper_id,
                    'so_shipper_of_id' => $row->jik_of_shipper_id,
                    'so_shipper_cp_id' => $row->jik_pic_shipper_id,
                    'so_notify_id' => $row->jik_notify_id,
                    'so_notify_of_id' => $row->jik_of_notify_id,
                    'so_notify_cp_id' => $row->jik_pic_notify_id,
                    'so_old_transport' => $row->jik_transport_name,
                    'so_transport_number' => $row->jik_voyage_number,
                    'so_sppd_ref' => $row->jik_sppd_ref,
                    'so_sppd_date' => $row->jik_sppd_date,
                    'so_do_ref' => $row->jik_do_ref,
                    'so_do_expired' => $row->jik_do_expired,
                    'so_manifest_ref' => $row->jik_manifest_ref,
                    'so_manifest_date' => $row->jik_manifest_date,
                    'so_manifest_pos' => $row->jik_manifest_pos,
                    'so_manifest_sub_pos' => $row->jik_manifest_sub_pos,
                ]);
        }
        $sqlWh = 'SELECT jo.jo_id, jo.jo_so_id, jo.jo_srt_id, (CASE WHEN ji.ji_id IS NULL THEN job.job_wh_id ELSE ji.ji_wh_id END) as jo_wh_id
                    FROM job_order as jo
                     LEFT OUTER JOIN job_inbound as ji ON jo.jo_id = ji.ji_jo_id
                     LEFT OUTER JOIN job_outbound as job ON jo.jo_id = job.job_jo_id
                    WHERE jo.jo_deleted_on IS NULL AND jo.jo_so_id IS NOT NULL
                    AND jo.jo_srt_id IN (1, 2)';
        $whResults = DB::select($sqlWh);
        foreach ($whResults as $row) {
            DB::table('sales_order')->where('so_id', $row->jo_so_id)
                ->update([
                    'so_warehouse' => 'Y',
                    'so_wh_id' => $row->jo_wh_id
                ]);
        }
        $query = 'SELECT so_id, so_ss_id, so_inklaring, so_delivery, so_warehouse from sales_order';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            $inklaring = 'Y';
            if (empty($row->so_inklaring) === true) {
                $inklaring = 'N';
            }
            $warehouse = 'Y';
            if (empty($row->so_warehouse) === true) {
                $warehouse = 'N';
            }
            DB::table('sales_order')->where('so_id', $row->so_id)
                ->update([
                    'so_consolidate' => 'N',
                    'so_inklaring' => $inklaring,
                    'so_delivery' => 'N',
                    'so_multi_load' => 'N',
                    'so_multi_unload' => 'N',
                    'so_warehouse' => $warehouse,
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
    }
}
