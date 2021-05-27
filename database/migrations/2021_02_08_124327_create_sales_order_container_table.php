<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;

class CreateSalesOrderContainerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_container', function (Blueprint $table) {
            $table->bigIncrements('soc_id');
            $table->string('soc_number', 64);
            $table->bigInteger('soc_so_id')->unsigned();
            $table->foreign('soc_so_id', 'tbl_soc_so_id_foreign')->references('so_id')->on('sales_order');
            $table->bigInteger('soc_eg_id')->unsigned()->nullable();
            $table->foreign('soc_eg_id', 'tbl_soc_eg_id_foreign')->references('eg_id')->on('equipment_group');
            $table->bigInteger('soc_ct_id')->unsigned()->nullable();
            $table->foreign('soc_ct_id', 'tbl_soc_ct_id_foreign')->references('ct_id')->on('container');
            $table->string('soc_container_number', 128)->nullable();
            $table->string('soc_seal_number', 128)->nullable();
            $table->bigInteger('soc_joc_id')->unsigned()->nullable();
            $table->foreign('soc_joc_id', 'tbl_soc_joc_id_foreign')->references('joc_id')->on('job_container');
            $table->bigInteger('soc_created_by');
            $table->dateTime('soc_created_on');
            $table->bigInteger('soc_updated_by')->nullable();
            $table->dateTime('soc_updated_on')->nullable();
            $table->bigInteger('soc_deleted_by')->nullable();
            $table->dateTime('soc_deleted_on')->nullable();
            $table->string('soc_deleted_reason', 256)->nullable();
            $table->uuid('soc_uid');
            $table->unique('soc_uid', 'tbl_soc_uid_unique');
        });
        $query = "SELECT jo.jo_id, jo.jo_ss_id, jo.jo_so_id, jo.jo_number, joc.joc_id, joc.joc_ct_id,
                        joc.joc_container_number, joc.joc_seal_number
                FROM job_order as jo
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                INNER JOIN job_container as joc ON jo.jo_id = joc.joc_jo_id
                WHERE jo.jo_deleted_on is null
                  and joc.joc_deleted_on is null
                and jo.jo_srv_id = 2
                and srt.srt_container = 'Y'
                and jo.jo_so_id is not null
                and jo.jo_id <> 7367";
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        $mbsNumber = 1;
        $wlogNumber = 1;
        $uid = 1;
        foreach ($sqlResults as $row) {
            if ((int)$row->jo_ss_id === 2) {
                $number = 'CT-00' . $mbsNumber;
                $mbsNumber++;
            } else {
                $number = 'CT-00' . $wlogNumber;
                $wlogNumber++;
            }
            $uidKey = Uuid::uuid3(Uuid::NAMESPACE_URL, microtime() . 'soc' . $uid);
            DB::table('sales_order_container')->insert([
                'soc_number' => $number,
                'soc_so_id' => $row->jo_so_id,
                'soc_ct_id' => $row->joc_ct_id,
                'soc_container_number' => $row->joc_container_number,
                'soc_seal_number' => $row->joc_seal_number,
                'soc_joc_id' => $row->joc_id,
                'soc_uid' => $uidKey,
                'soc_created_on' => date('Y-m-d H:i:s'),
                'soc_created_by' => 1
            ]);
            $uid++;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('sales_order_container');
    }
}
