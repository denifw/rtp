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
            MenuSeeder::class,
            PageCategorySeeder::class,
            PageSeeder::class,
            PageRightSeeder::class,
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            DistrictSeeder::class,
            LanguageSeeder::class,
            CurrencySeeder::class,
            SystemSettingSeeder::class,
            RelationSeeder::class,
            OfficeSeeder::class,
            ContactPersonSeeder::class,
            UserSeeder::class,
            UserMappingSeeder::class
        ]);
    }
}
