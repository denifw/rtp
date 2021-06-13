<?php

use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_group')->insert(['usg_id' => '76a6fd86-edb8-3501-ae74-6e4833d5312b', 'usg_name' => 'Admin', 'usg_active' => 'Y', 'usg_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'usg_created_on' => date('Y-m-d H:i:s')]);
    }
}
