<?php

use Illuminate\Database\Seeder;

class ResetLocalPassword extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->update(['us_password' => bcrypt('localhost')]);

    }
}
