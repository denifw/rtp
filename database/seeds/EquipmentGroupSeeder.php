<?php

use Illuminate\Database\Seeder;

class EquipmentGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('equipment_group')
            ->where('eg_id', 1)
            ->update(['eg_tm_id' => 1, 'eg_container' => 'N']);
        DB::table('equipment_group')
            ->where('eg_id', 2)
            ->update(['eg_tm_id' => 1, 'eg_name' => 'Trailer 20 FT', 'eg_container' => 'Y']);
        DB::table('equipment_group')
            ->where('eg_id', 3)
            ->update(['eg_tm_id' => 1, 'eg_name' => 'Trailer 20 FT Flatbed', 'eg_container' => 'Y']);
        DB::table('equipment_group')
            ->where('eg_id', 4)
            ->update(['eg_tm_id' => 1, 'eg_name' => 'Trailer 40 FT', 'eg_container' => 'Y']);
        DB::table('equipment_group')
            ->where('eg_id', 5)
            ->update(['eg_tm_id' => 1, 'eg_name' => 'Trailer 40 FT Flatbed', 'eg_container' => 'Y']);
        DB::table('equipment_group')
            ->where('eg_id', 6)
            ->update(['eg_tm_id' => 1, 'eg_container' => 'N']);
        DB::table('equipment_group')
            ->where('eg_id', 7)
            ->update(['eg_tm_id' => 1, 'eg_name' => 'Trailer 20 FT HC', 'eg_container' => 'Y']);
        DB::table('equipment_group')->insert(['eg_name' => 'Container Ships', 'eg_container' => 'Y', 'eg_code' => 'Container', 'eg_tm_id' => 3, 'eg_active' => 'Y', 'eg_uid' => '819fe9b7-8d06-3249-b8fa-78e36c6387ce', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Bulk Carrier', 'eg_container' => 'N', 'eg_code' => 'BulkCarrier', 'eg_tm_id' => 3, 'eg_active' => 'Y', 'eg_uid' => '830fa07b-0f23-30c3-a9a2-394a4e9471bb', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Tanker Ships', 'eg_container' => 'N', 'eg_code' => 'Tanker', 'eg_tm_id' => 3, 'eg_active' => 'Y', 'eg_uid' => '31672574-8ee7-360f-80de-d12a275269d0', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Passenger Ships', 'eg_container' => 'N', 'eg_code' => 'Passenger', 'eg_tm_id' => 3, 'eg_active' => 'Y', 'eg_uid' => '1ded48ea-444d-37e1-a3c1-93ebe3045289', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Naval Ships', 'eg_container' => 'N', 'eg_code' => 'Naval', 'eg_tm_id' => 3, 'eg_active' => 'Y', 'eg_uid' => 'fe57906c-69a7-38fa-9d58-790feeeec3bd', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Offshore Ships', 'eg_container' => 'N', 'eg_code' => 'Offshore', 'eg_tm_id' => 3, 'eg_active' => 'Y', 'eg_uid' => '37dd82c5-a984-3e3b-9108-b625a944fee8', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Special Purpose Ships', 'eg_container' => 'N', 'eg_code' => 'Special', 'eg_tm_id' => 3, 'eg_active' => 'Y', 'eg_uid' => 'fc2e5177-180f-33f7-9a13-118fa5392b05', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Roll-on Roll-Off Ships', 'eg_container' => 'N', 'eg_code' => 'Roro', 'eg_tm_id' => 3, 'eg_active' => 'Y', 'eg_uid' => 'd6f3b84a-c17d-30e7-bd30-ba10f6796258', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Colt Diesel Engkel', 'eg_code' => 'CDE', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '1bccb473-7a81-38c1-949b-fc38d8206c7c', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'CDD Losbak', 'eg_code' => 'CDDLB', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '3903b5fc-b249-3e90-b34a-53041b925b5e', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'CDD Bak Kayu', 'eg_code' => 'CDDBK', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => 'b858b78a-6e2c-339b-a522-e1deb8380c5b', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Fuso Box', 'eg_code' => 'FB', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '1e93f63a-1dcc-3225-a9f9-d9eb547e27bb', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Fuso Losbak', 'eg_code' => 'FLB', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => 'c972e43b-8679-3dbc-a189-e5e36b3510cb', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Fuso Bak Kayu', 'eg_code' => 'FBK', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '260c8196-2133-3837-bb30-4782017b8b49', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Dump Truck 8 Ton', 'eg_code' => 'DT8', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '87c9399b-8293-3769-911a-fe4d706421af', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Dump Truck 25 Ton', 'eg_code' => 'DT25', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => 'c03a6d26-0e0e-3283-b698-b27909bbe8d5', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Tronton Wing Box', 'eg_code' => 'TWB', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => 'bf0831bb-f9c7-3ba5-9f25-5deb3d0f414b', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Tronton Losbak', 'eg_code' => 'TLB', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => 'f3207e76-ab3d-3791-8634-5b3fce9a8207', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Tronton Full Box', 'eg_code' => 'TFB', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '82c1cf9c-6dc2-33bc-bde3-787099dade4d', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Tronton Three Way', 'eg_code' => 'TFW', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '645b7e8c-919e-346e-b18e-11f61c99c604', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Lowbed', 'eg_code' => 'LB', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '3e4195b2-fd36-323b-80c1-654b73a7b418', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Big Mama', 'eg_code' => 'BM', 'eg_tm_id' => 1, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '3c094565-4fd8-3168-b8fa-3acaa7107f28', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Passenger Flight', 'eg_code' => 'PF', 'eg_tm_id' => 4, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '6ba8fae1-2d84-3649-8742-088a415fc78e', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Cargo Flight', 'eg_code' => 'CF', 'eg_tm_id' => 4, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => 'dadf94e5-ea8a-329d-a095-35d4096ce9b8', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Passenger Train', 'eg_code' => 'PT', 'eg_tm_id' => 2, 'eg_container' => 'N', 'eg_active' => 'Y', 'eg_uid' => '38b683bd-d1e8-3366-bfe7-f61837fe259a', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
        DB::table('equipment_group')->insert(['eg_name' => 'Container Train', 'eg_code' => 'FT', 'eg_tm_id' => 2, 'eg_container' => 'Y', 'eg_active' => 'Y', 'eg_uid' => 'da5c49d4-6e9a-3371-ab65-48a4fdb0e8c1', 'eg_created_on' => date('Y-m-d H:i:s'), 'eg_created_by' => 1]);
    }
}
