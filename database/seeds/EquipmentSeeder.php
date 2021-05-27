<?php

use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('equipment')->insert(['eq_ss_id' => 2, 'eq_rel_id' => 2, 'eq_number' => 'EQ-2100001', 'eq_description' => 'B 1234 CK', 'eq_eg_id' => 5, 'eq_manage_by_id' => 2, 'eq_license_plate' => 'B1234CK', 'eq_primary_meter' => 'km', 'eq_eqs_id' => 1, 'eq_active' => Y, 'eq_uid' => '3d90071f-e071-33c9-9b15-2a86ba38c04c', 'eq_created_on' => date('Y-m-d H:i:s'), 'eq_created_by' => 1]);
        DB::table('equipment')->insert(['eq_ss_id' => 2, 'eq_rel_id' => 2, 'eq_number' => 'EQ-2100002', 'eq_description' => 'B 2345 CK', 'eq_eg_id' => 5, 'eq_manage_by_id' => 2, 'eq_license_plate' => 'B2345CK', 'eq_primary_meter' => 'km', 'eq_eqs_id' => 1, 'eq_active' => Y, 'eq_uid' => 'fef1d9d5-2522-3953-955a-0901d6f217f9', 'eq_created_on' => date('Y-m-d H:i:s'), 'eq_created_by' => 1]);
        DB::table('equipment')->insert(['eq_ss_id' => 2, 'eq_rel_id' => 531, 'eq_number' => 'EQ-2100003', 'eq_description' => 'KM Meratus 101', 'eq_eg_id' => 8, 'eq_manage_by_id' => 531, 'eq_license_plate' => '234123', 'eq_primary_meter' => 'km', 'eq_eqs_id' => 1, 'eq_active' => Y, 'eq_uid' => '4a935164-8f7b-30c5-a5f7-a4d3a3d883d8', 'eq_created_on' => date('Y-m-d H:i:s'), 'eq_created_by' => 1]);
        DB::table('equipment')->insert(['eq_ss_id' => 2, 'eq_rel_id' => 531, 'eq_number' => 'EQ-2100004', 'eq_description' => 'KM Meratus 102', 'eq_eg_id' => 8, 'eq_manage_by_id' => 531, 'eq_license_plate' => '1234565', 'eq_primary_meter' => 'km', 'eq_eqs_id' => 1, 'eq_active' => Y, 'eq_uid' => '14490c3b-965d-38e7-a70a-25124aa35990', 'eq_created_on' => date('Y-m-d H:i:s'), 'eq_created_by' => 1]);
    }
}
