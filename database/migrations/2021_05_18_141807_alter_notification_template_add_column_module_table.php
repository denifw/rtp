<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNotificationTemplateAddColumnModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_template', function (Blueprint $table) {
            $table->dropUnique('tbl_nt_code_unique');
            $table->string('nt_module', 256)->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => NotificationTemplateSeeder::class,
        ]);
        Schema::table('notification_template', function (Blueprint $table) {
            $table->unique(['nt_code', 'nt_module'], 'tbl_nt_code_module_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_template', function (Blueprint $table) {
            $table->dropUnique('tbl_nt_code_module_unique');
            $table->dropColumn('nt_module');
        });
    }
}
