<?php

use Illuminate\Database\Seeder;

class UserGroupRightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_group_right')->insert(['ugr_id' => '8b26fab6-3068-36ad-92df-57fabbd9f186', 'ugr_usg_id' => '76a6fd86-edb8-3501-ae74-6e4833d5312b', 'ugr_pr_id' => '0cecacc6-ecd4-372a-bf6b-f741a19d6f1d', 'ugr_created_by' => '47e71f7c-548c-36ad-8ba7-52652a4698bc', 'ugr_created_on' => date('Y-m-d H:i:s')]);
    }
}
