<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank', function (Blueprint $table) {
            $table->uuid('bn_id')->primary();
            $table->uuid('bn_ss_id')->unsigned();
            $table->foreign('bn_ss_id', 'tbl_bn_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('bn_short_name', 128);
            $table->string('bn_name', 256);
            $table->char('bn_active', 1)->default('Y');
            $table->uuid('bn_created_by');
            $table->dateTime('bn_created_on');
            $table->uuid('bn_updated_by')->nullable();
            $table->dateTime('bn_updated_on')->nullable();
            $table->uuid('bn_deleted_by')->nullable();
            $table->dateTime('bn_deleted_on')->nullable();
            $table->string('bn_deleted_reason', 255)->nullable();
            $table->unique(['bn_ss_id', 'bn_short_name'], 'tbl_bn_ss_short_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => BankSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank');
    }
}
