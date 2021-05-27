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
        DB::table('user_group')->insert(['usg_ss_id' => 2, 'usg_name' => 'New Administrator', 'usg_active' => 'Y', 'usg_uid' => 'e1f3fd78-41e9-3fbf-a397-79e8b8bea427', 'usg_created_on' => date('Y-m-d H:i:s'), 'usg_created_by' => 1]);
    }
}
