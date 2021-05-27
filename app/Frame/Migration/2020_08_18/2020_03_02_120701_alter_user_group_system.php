<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserGroupSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('user_group_right')->truncate();
        DB::table('user_group_page')->truncate();
        DB::table('user_group_notification')->truncate();
        DB::table('user_group_api_access')->truncate();
        DB::table('user_group_detail')->truncate();
        DB::table('user_group')->truncate();
        Schema::table('user_group', function (Blueprint $table) {
            $table->dropUnique('tbl_usg_name_unique');
            $table->bigInteger('usg_ss_id')->unsigned();
            $table->foreign('usg_ss_id', 'tbl_usg_ss_id_foreign')->references('ss_id')->on('system_setting');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupSeeder::class,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupPageSeeder::class,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupRightSeeder::class,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupNotificationSeeder::class,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupApiSeeder::class,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserGroupDetailSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
