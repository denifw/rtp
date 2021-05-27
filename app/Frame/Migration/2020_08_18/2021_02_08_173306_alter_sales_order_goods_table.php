<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSalesOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('sales_order_goods');
        Schema::create('sales_order_goods', function (Blueprint $table) {
            $table->bigIncrements('sog_id');
            $table->string('sog_number', 128);
            $table->bigInteger('sog_so_id')->unsigned();
            $table->foreign('sog_so_id', 'tbl_sog_so_id_foreign')->references('so_id')->on('sales_order');
            $table->bigInteger('sog_soc_id')->unsigned()->nullable();
            $table->foreign('sog_soc_id', 'tbl_sog_soc_id_foreign')->references('soc_id')->on('sales_order_container');
            $table->string('sog_hs_code', 256)->nullable();
            $table->string('sog_name', 256);
            $table->string('sog_packing_ref', 256)->nullable();
            $table->float('sog_quantity')->nullable();
            $table->bigInteger('sog_uom_id')->unsigned()->nullable();
            $table->foreign('sog_uom_id', 'tbl_sog_uom_id_foreign')->references('uom_id')->on('unit');
            $table->float('sog_length')->nullable();
            $table->float('sog_width')->nullable();
            $table->float('sog_height')->nullable();
            $table->float('sog_cbm')->nullable();
            $table->float('sog_gross_weight')->nullable();
            $table->float('sog_net_weight')->nullable();
            $table->string('sog_dimension_unit', '1')->nullable();
            $table->string('sog_notes', '256')->nullable();
            $table->bigInteger('sog_jog_id')->unsigned()->nullable();
            $table->foreign('sog_jog_id', 'tbl_sog_jog_id_foreign')->references('jog_id')->on('job_goods');
            $table->bigInteger('sog_created_by');
            $table->dateTime('sog_created_on');
            $table->bigInteger('sog_updated_by')->nullable();
            $table->dateTime('sog_updated_on')->nullable();
            $table->bigInteger('sog_deleted_by')->nullable();
            $table->dateTime('sog_deleted_on')->nullable();
            $table->string('sog_deleted_reason', 256)->nullable();
            $table->uuid('sog_uid');
            $table->unique('sog_uid', 'tbl_sog_uid_unique');
        });
        # Load Sales order Container Data.
        $cntQurty = 'select soc_id, soc_so_id FROM sales_order_container';
        $sqlResults = \Illuminate\Support\Facades\DB::select($cntQurty);
        $containers = [];
        foreach ($sqlResults as $row) {
            $soId = (int)$row->soc_so_id;
            if (array_key_exists($soId, $containers) === false) {
                $containers[$soId] = [];
            }
            $containers[$soId][] = [
                'soc_id' => $row->soc_id
            ];
        }
        # Load Inklaring goods data.
        $gdQuery = 'SELECT jo.jo_id, jo.jo_ss_id, jo.jo_so_id, jo.jo_number, jog.jog_id,
                           jog.jog_name, jog.jog_quantity, jog.jog_uom_id, jog.jog_production_number,
                            jog.jog_weight, jog.jog_volume
                    FROM job_order as jo
                    INNER JOIN job_goods as jog ON jo.jo_id = jog.jog_jo_id
                    WHERE jo.jo_deleted_on is null
                      and jog.jog_deleted_on is null
                    and jo.jo_srv_id = 2
                    and jo.jo_so_id is not null
                    and jo.jo_id <> 7367';
        $sqlGdResults = \Illuminate\Support\Facades\DB::select($gdQuery);
        $uid = 1;
        $number = 1;
        foreach ($sqlGdResults as $row) {
            $soId = (int)$row->jo_so_id;
            if (array_key_exists($soId, $containers) === true) {
                $container = $containers[$soId];
                foreach ($container as $cn) {
                    $uidKey = \Ramsey\Uuid\Uuid::uuid3(\Ramsey\Uuid\Uuid::NAMESPACE_URL, microtime() . 'sog' . $uid);
                    DB::table('sales_order_goods')->insert([
                        'sog_number' => 'SOG-000' . $number,
                        'sog_so_id' => $row->jo_so_id,
                        'sog_soc_id' => $cn['soc_id'],
                        'sog_hs_code' => $row->jog_production_number,
                        'sog_name' => $row->jog_name,
                        'sog_quantity' => $row->jog_quantity,
                        'sog_uom_id' => $row->jog_uom_id,
                        'sog_net_weight' => $row->jog_weight,
                        'sog_cbm' => $row->jog_volume,
                        'sog_dimension_unit' => 'Y',
                        'sog_jog_id' => $row->jog_id,
                        'sog_uid' => $uidKey,
                        'sog_created_on' => date('Y-m-d H:i:s'),
                        'sog_created_by' => 1
                    ]);
                    $number++;
                    $uid++;
                }
            } else {
                $uidKey = \Ramsey\Uuid\Uuid::uuid3(\Ramsey\Uuid\Uuid::NAMESPACE_URL, microtime() . 'sog' . $uid);
                DB::table('sales_order_goods')->insert([
                    'sog_number' => 'SOG-000' . $number,
                    'sog_so_id' => $row->jo_so_id,
                    'sog_hs_code' => $row->jog_production_number,
                    'sog_name' => $row->jog_name,
                    'sog_quantity' => $row->jog_quantity,
                    'sog_uom_id' => $row->jog_uom_id,
                    'sog_gross_weight' => $row->jog_weight,
                    'sog_cbm' => $row->jog_volume,
                    'sog_dimension_unit' => 'Y',
                    'sog_jog_id' => $row->jog_id,
                    'sog_uid' => $uidKey,
                    'sog_created_on' => date('Y-m-d H:i:s'),
                    'sog_created_by' => 1
                ]);
                $number++;
                $uid++;
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
//        Schema::dropIfExists('sales_order_goods');
    }
}
