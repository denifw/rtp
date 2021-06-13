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
            # System - Page
            SystemTableSeeder::class,
            MenuSeeder::class,
            PageCategorySeeder::class,
            PageSeeder::class,
            PageRightSeeder::class,
            # System - Master
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            DistrictSeeder::class,
            LanguageSeeder::class,
            CurrencySeeder::class,
            BankSeeder::class,
            UnitSeeder::class,
            SerialCodeSeeder::class,
            ServiceSeeder::class,
            # System - Access
            SystemSettingSeeder::class,
            SystemServiceSeeder::class,
            RelationSeeder::class,
            OfficeSeeder::class,
            ContactPersonSeeder::class,
            UserSeeder::class,
            UserMappingSeeder::class,
            # System - Document
            DocumentGroupSeeder::class,
            DocumentTypeSeeder::class,
            DocumentTemplateSeeder::class,
            DocumentTemplateTypeSeeder::class,
            DocumentSeeder::class,
        ]);
    }
}
