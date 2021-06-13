<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_service', function (Blueprint $table) {
            $table->uuid('ssr_id')->primary();
            $table->uuid('ssr_ss_id')->unsigned();
            $table->foreign('ssr_ss_id', 'tbl_ssr_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->uuid('ssr_srv_id')->unsigned();
            $table->foreign('ssr_srv_id', 'tbl_ssr_srv_id_fkey')->references('srv_id')->on('service');
            $table->char('ssr_active', 1)->default('Y');
            $table->uuid('ssr_created_by');
            $table->dateTime('ssr_created_on');
            $table->uuid('ssr_updated_by')->nullable();
            $table->dateTime('ssr_updated_on')->nullable();
            $table->uuid('ssr_deleted_by')->nullable();
            $table->dateTime('ssr_deleted_on')->nullable();
            $table->string('ssr_deleted_reason', 256)->nullable();
            $table->unique(['ssr_srv_id', 'ssr_ss_id'], 'tbl_ssr_srv_ss_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_service');
    }
}
