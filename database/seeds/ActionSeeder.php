<?php

use Illuminate\Database\Seeder;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('action')
            ->where('ac_id', 12)
            ->update([
                'ac_order' => 4,
            ]);
        DB::table('action')
            ->where('ac_id', 13)
            ->update([
                'ac_order' => 5,
            ]);
        DB::table('action')
            ->where('ac_id', 14)
            ->update([
                'ac_code' => 'GatePass',
                'ac_description' => 'Gate Pass',
                'ac_order' => 6,
            ]);
        DB::table('action')
            ->where('ac_id', 19)
            ->update([
                'ac_code' => 'GatePass',
                'ac_description' => 'Gate Pass'
            ]);
        DB::table('action')
            ->where('ac_id', 22)
            ->update([
                'ac_order' => 4,
            ]);
        DB::table('action')
            ->where('ac_id', 23)
            ->update([
                'ac_order' => 5,
            ]);
        DB::table('action')
            ->where('ac_id', 24)
            ->update([
                'ac_code' => 'GatePass',
                'ac_description' => 'Gate Pass',
                'ac_order' => 6,
            ]);
        DB::table('action')
            ->where('ac_id', 29)
            ->update([
                'ac_code' => 'GatePass',
                'ac_description' => 'Gate Pass'
            ]);
        DB::table('action')
            ->where('ac_id', 30)
            ->update([
                'ac_code' => 'Loading',
                'ac_description' => 'Loading Goods'
            ]);
        DB::table('action')
            ->where('ac_id', 31)
            ->update([
                'ac_code' => 'Unload',
                'ac_description' => 'Unload Goods'
            ]);
        DB::table('action')->insert(['ac_srt_id' => 16, 'ac_code' => 'Loading', 'ac_description' => 'Loading Goods', 'ac_order' => 1, 'ac_style' => 'primary', 'ac_uid' => '16f74a3e-f53c-38e3-b4fc-06d1367b7f3f', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 16, 'ac_code' => 'Unload', 'ac_description' => 'Unload Goods', 'ac_order' => 2, 'ac_style' => 'primary', 'ac_uid' => '23b0647a-5612-3654-8ef8-3b8ee2f01fa7', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 16, 'ac_code' => 'Pool', 'ac_description' => 'Back To Pool', 'ac_order' => 3, 'ac_style' => 'primary', 'ac_uid' => 'bb0d26ec-b79b-395f-8e97-ddff2f33af6b', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 17, 'ac_code' => 'Loading', 'ac_description' => 'Loading Goods', 'ac_order' => 1, 'ac_style' => 'primary', 'ac_uid' => '9a3a8ab9-3e89-3b6e-8242-af4983eed19d', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 17, 'ac_code' => 'Unload', 'ac_description' => 'Unload Goods', 'ac_order' => 2, 'ac_style' => 'primary', 'ac_uid' => '8436fa79-ca9f-3057-9435-b5154702db3f', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 17, 'ac_code' => 'Pool', 'ac_description' => 'Back to Pool', 'ac_order' => 3, 'ac_style' => 'primary', 'ac_uid' => '4fa31b8f-a089-3643-b5a7-b91a3780734d', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 19, 'ac_code' => 'Loading', 'ac_description' => 'Loading Goods', 'ac_order' => 1, 'ac_style' => 'primary', 'ac_uid' => 'ed3b9226-a8c7-3d8a-b708-3e0a09337de8', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 19, 'ac_code' => 'Unload', 'ac_description' => 'Unload Goods', 'ac_order' => 2, 'ac_style' => 'primary', 'ac_uid' => 'b7b54861-c3ce-3f25-a28b-c574aab2f61d', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 18, 'ac_code' => 'Loading', 'ac_description' => 'Loading Container', 'ac_order' => 1, 'ac_style' => 'primary', 'ac_uid' => 'f8bfea6f-322e-3cad-b8e5-ccb261b7aa92', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 18, 'ac_code' => 'Unload', 'ac_description' => 'Unload Container', 'ac_order' => 2, 'ac_style' => 'primary', 'ac_uid' => '6811b445-f9f5-3a87-9f73-3d770cc1d79c', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 15, 'ac_code' => 'PickUp', 'ac_description' => 'Pick Up Empty Container', 'ac_order' => 1, 'ac_style' => 'primary', 'ac_uid' => '297679e5-bda6-372c-9e5e-44c28bdd30da', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 15, 'ac_code' => 'Loading', 'ac_description' => 'Loading Goods', 'ac_order' => 2, 'ac_style' => 'primary', 'ac_uid' => 'cd43672b-8233-3a7a-a542-f83c3b57cb57', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 15, 'ac_code' => 'Unload', 'ac_description' => 'Unload Goods', 'ac_order' => 3, 'ac_style' => 'primary', 'ac_uid' => '9c80dd25-3cde-320f-b273-a71a4bc9ae0e', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 15, 'ac_code' => 'Return', 'ac_description' => 'Return Container', 'ac_order' => 4, 'ac_style' => 'primary', 'ac_uid' => '39f0caea-54dd-37de-ab0b-2962c5ec76ac', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 15, 'ac_code' => 'Pool', 'ac_description' => 'Back to Pool', 'ac_order' => 5, 'ac_style' => 'primary', 'ac_uid' => 'f6c27ef4-a863-3752-b5e5-2e1c82d5dde4', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        # New Inkalring Action
        DB::table('action')->insert(['ac_srt_id' => 6, 'ac_code' => 'Arrive', 'ac_description' => 'Transport Arrive', 'ac_order' => 3, 'ac_style' => 'warning', 'ac_uid' => 'e7490a41-eb6e-3e20-aff6-9fda9be07f45', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 7, 'ac_code' => 'Departure', 'ac_description' => 'Transport Departure', 'ac_order' => 6, 'ac_style' => 'warning', 'ac_uid' => '802695a2-d021-3674-99a4-f7f045ba6830', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 8, 'ac_code' => 'Arrive', 'ac_description' => 'Transport Arrive', 'ac_order' => 3, 'ac_style' => 'warning', 'ac_uid' => '3e9c28ec-c4fb-303f-9a3f-72efb9298e0f', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
        DB::table('action')->insert(['ac_srt_id' => 9, 'ac_code' => 'Departure', 'ac_description' => 'Transport Departure', 'ac_order' => 6, 'ac_style' => 'warning', 'ac_uid' => '94f57316-a657-3916-87af-074798df0b95', 'ac_created_on' => date('Y-m-d H:i:s'), 'ac_created_by' => 1]);
    }
}
