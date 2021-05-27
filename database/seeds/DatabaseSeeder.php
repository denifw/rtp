<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        # Already run at trial system.
        $this->call([
            SystemTableSeeder::class,
        ]);
    }
}
