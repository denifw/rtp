<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_mapping', function (Blueprint $table) {
            $table->bigIncrements('ump_id');
            $table->bigInteger('ump_us_id')->nullable();
            $table->foreign('ump_us_id', 'tbl_ump_us_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('ump_ss_id')->nullable();
            $table->foreign('ump_ss_id', 'tbl_ump_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('ump_rel_id')->nullable();
            $table->foreign('ump_rel_id', 'tbl_ump_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('ump_cp_id')->nullable();
            $table->foreign('ump_cp_id', 'tbl_ump_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->string('ump_api_token', 32);
            $table->char('ump_confirm', 1)->default('N');
            $table->char('ump_default', 1)->default('N');
            $table->char('ump_active', 1)->default('Y');
            $table->bigInteger('ump_created_by');
            $table->dateTime('ump_created_on');
            $table->bigInteger('ump_updated_by')->nullable();
            $table->dateTime('ump_updated_on')->nullable();
            $table->bigInteger('ump_deleted_by')->nullable();
            $table->dateTime('ump_deleted_on')->nullable();
            $table->unique(['ump_ss_id', 'ump_us_id'], 'tbl_ump_ss_id_us_id_unique');
            $table->unique(['ump_ss_id', 'ump_cp_id'], 'tbl_ump_ss_id_cp_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UserMappingSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_mapping');
    }
}
