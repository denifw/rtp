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
            MenuSeeder::class,
            PageSeeder::class,
            PageRightSeeder::class,
            ServiceTermSeeder::class,
            SystemTableSeeder::class,
            DocumentGroupSeeder::class,
            DocumentTypeSeeder::class,
            DocumentTemplateTypeSeeder::class,
            DocumentTemplateSeeder::class,
            SerialCodeSeeder::class,
            SerialNumberSeeder::class,
            # User Group
            UserGroupSeeder::class,
            UserGroupPageSeeder::class,
            UserGroupRightSeeder::class,
            UserGroupApiSeeder::class,
            UserGroupNotificationSeeder::class,
            UserGroupDashboardItemSeeder::class,

            # User Seeder
            UserSeeder::class,
            ContactPersonSeeder::class,
            UserMappingSeeder::class
        ]);
    }
}
