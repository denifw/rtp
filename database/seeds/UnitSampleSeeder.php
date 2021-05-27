<?php

use Illuminate\Database\Seeder;

class UnitSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('unit')->insert(['uom_name' => 'Sak', 'uom_code' => 'Sak', 'uom_active' => 'Y', 'uom_ss_id' => 2, 'uom_created_on' => date('Y-m-d H:i:s'), 'uom_created_by' => 1]);
        DB::table('unit')->insert(['uom_name' => 'Meter Cubik', 'uom_code' => 'M3', 'uom_active' => 'Y', 'uom_ss_id' => 2, 'uom_created_on' => date('Y-m-d H:i:s'), 'uom_created_by' => 1]);
        DB::table('unit')->insert(['uom_name' => 'Kilogram', 'uom_code' => 'KG', 'uom_active' => 'Y', 'uom_ss_id' => 2, 'uom_created_on' => date('Y-m-d H:i:s'), 'uom_created_by' => 1]);
        DB::table('unit')->insert(['uom_name' => 'Gram', 'uom_code' => 'G', 'uom_active' => 'Y', 'uom_ss_id' => 2, 'uom_created_on' => date('Y-m-d H:i:s'), 'uom_created_by' => 1]);
        DB::table('unit')->insert(['uom_name' => 'Meter Persegi', 'uom_code' => 'M2', 'uom_active' => 'Y', 'uom_ss_id' => 2, 'uom_created_on' => date('Y-m-d H:i:s'), 'uom_created_by' => 1]);
    }
}
