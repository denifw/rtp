<?php

use Illuminate\Database\Seeder;

class UserGroupDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_group_detail')->insert(['ugd_id' => '42873ef7-aba7-32b4-909c-b87a4883aff2', 'ugd_usg_id' => '76a6fd86-edb8-3501-ae74-6e4833d5312b', 'ugd_ump_id' => '2727f669-205b-3778-bd2a-4cae333dca0d', 'ugd_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ugd_created_on' => date('Y-m-d H:i:s')]);
    }
}
