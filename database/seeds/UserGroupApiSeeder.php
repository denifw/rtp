<?php

use Illuminate\Database\Seeder;

class UserGroupApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_group_api_access')->insert(['uga_usg_id' => 63, 'uga_aa_id' => 1, 'uga_uid' => '9cccc9f3-bee9-31c6-97f0-31368c210c28', 'uga_created_on' => date('Y-m-d H:i:s'), 'uga_created_by' => 1]);
        DB::table('user_group_api_access')->insert(['uga_usg_id' => 63, 'uga_aa_id' => 12, 'uga_uid' => '2ce026de-546d-3e88-a5c5-9a8f64c3244d', 'uga_created_on' => date('Y-m-d H:i:s'), 'uga_created_by' => 1]);
    }
}
