<?php

use Illuminate\Database\Seeder;

class EquipmentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('equipment_status')->insert(['eqs_name' => 'Available', 'eqs_active' => 'Y', 'eqs_created_on' => date('Y-m-d H:i:s'), 'eqs_created_by' => 1]);
        DB::table('equipment_status')->insert(['eqs_name' => 'Not Available', 'eqs_active' => 'Y', 'eqs_created_on' => date('Y-m-d H:i:s'), 'eqs_created_by' => 1]);
        DB::table('equipment_status')->insert(['eqs_name' => 'On Service', 'eqs_active' => 'Y', 'eqs_created_on' => date('Y-m-d H:i:s'), 'eqs_created_by' => 1]);

    }
}
