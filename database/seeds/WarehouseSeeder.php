<?php

use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('warehouse')->insert(['wh_ss_id' => 2, 'wh_of_id' => 5, 'wh_name' => 'Gudang Dadap', 'wh_length' => 50, 'wh_height' => 20, 'wh_width' => 100, 'wh_volume' => 100000, 'wh_active' => 'Y', 'wh_created_on' => date('Y-m-d H:i:s'), 'wh_created_by' => 1]);
    }
}
