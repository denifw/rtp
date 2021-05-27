<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSogAddStatusFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_goods', function (Blueprint $table) {
            $table->dateTime('sog_inklaring_on')->nullable();
            $table->dateTime('sog_delivery_on')->nullable();
            $table->dateTime('sog_warehouse_on')->nullable();
        });
        # Update status goods.
        $query = 'SELECT so.so_id, so.so_inklaring, so.so_warehouse, so.so_finish_on, sog.sog_id
                    FROM sales_order_goods as sog
                    INNER JOIN sales_order as so ON so.so_id = sog.sog_so_id
                    and so.so_finish_on is not null';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        foreach ($sqlResults as $row) {
            $inklaring = null;
            $warehouse = null;
            if ($row->so_inklaring === 'Y') {
                $inklaring = $row->so_finish_on;
            }
            if ($row->so_warehouse === 'Y') {
                $warehouse = $row->so_finish_on;
            }
            DB::table('sales_order_goods')->where('sog_id', $row->sog_id)
                ->update([
                    'sog_inklaring_on' => $inklaring,
                    'sog_warehouse_on' => $warehouse,
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
