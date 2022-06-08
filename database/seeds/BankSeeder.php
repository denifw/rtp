<?php

use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bank')->insert(['bn_id' => '1291da97-196b-3009-ba44-48d1200da86c', 'bn_ss_id'=> 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8','bn_short_name' => 'CIMB', 'bn_name' => 'Bank CIMB Niaga', 'bn_active' => 'Y', 'bn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'bn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('bank')->insert(['bn_id' => 'fbf62483-f4d9-38c4-b659-26ff48008211', 'bn_ss_id'=> 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'bn_short_name' => 'BRI', 'bn_name' => 'Bank Rakyat Indonesia', 'bn_active' => 'Y', 'bn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'bn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('bank')->insert(['bn_id' => '514d4f4d-a48c-3170-8d93-8813357d1955', 'bn_ss_id'=> 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'bn_short_name' => 'Mandiri', 'bn_name' => 'Bank Mandiri', 'bn_active' => 'Y', 'bn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'bn_created_on' => date('Y-m-d H:i:s')]);
        DB::table('bank')->insert(['bn_id' => '8128785b-6900-3d0d-ac22-63d079a0313e', 'bn_ss_id'=> 'a629c5e3-2dd5-3a10-a7e9-a04cc0d6dff8', 'bn_short_name' => 'BCA', 'bn_name' => 'Bank Central Asia', 'bn_active' => 'Y', 'bn_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'bn_created_on' => date('Y-m-d H:i:s')]);
    }
}
