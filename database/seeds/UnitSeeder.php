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
        DB::table('unit')->insert(['uom_id' => '575f530e-9d92-398f-b413-c942e5bb6dc7', 'uom_name' => 'Kilogram', 'uom_code' => 'KG', 'uom_active' => 'Y', 'uom_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'uom_created_on' => date('Y-m-d H:i:s')]);
    }
}
