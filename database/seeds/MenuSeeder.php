<?php

use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menu')->where('mn_id', 29)->update([
            'mn_name' => 'Cash And Bank',
        ]);
    }
}
