<?php

use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('unit')->insert(['uom_name' => 'Pieces', 'uom_code' => 'PCS', 'uom_active' => 'Y', 'uom_created_on' => date('Y-m-d H:i:s'), 'uom_created_by' => 1]);
    }
}
