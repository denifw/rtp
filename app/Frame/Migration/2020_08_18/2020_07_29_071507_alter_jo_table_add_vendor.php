<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJoTableAddVendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('job_order', function (Blueprint $table) {
            $table->bigInteger('jo_vendor_id')->unsigned()->nullable();
            $table->foreign('jo_vendor_id', 'tbl_jo_vendor_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('jo_vendor_pic_id')->unsigned()->nullable();
            $table->foreign('jo_vendor_pic_id', 'tbl_jo_vendor_pic_id_foreign')->references('cp_id')->on('contact_person');
        });
        $query = 'select jo.jo_id, jo_ss_id, jo.jo_srv_id, jt.jt_rel_id
                    from job_order as jo left outer join
                        job_trucking as jt ON jo.jo_id = jt.jt_jo_id
                    order by jo.jo_id';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        foreach ($sqlResults as $row) {
            $vendorId = null;
            if ((int)$row->jo_srv_id === 3) {
                $vendorId = $row->jt_rel_id;
            } else {
                $ssId = (int)$row->jo_ss_id;
                if ($ssId === 2) {
                    $vendorId = 2;
                } else if ($ssId === 4) {
                    $vendorId = 324;
                } else if ($ssId === 5) {
                    $vendorId = 533;
                }
            }
            DB::table('job_order')
                ->where('jo_id', $row->jo_id)
                ->update(['jo_vendor_id' => $vendorId]);
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
