<?php

use Illuminate\Database\Seeder;

class ResetMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('migrations')->truncate();
    }
}
