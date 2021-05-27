<?php

use Illuminate\Database\Seeder;

class SerialCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('serial_code')->where('sc_id', 8)->update([
            'sc_code' => 'BT',
            'sc_description' => 'Bank Transaction'
        ]);
    }
}
