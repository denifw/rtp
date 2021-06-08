<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service', function (Blueprint $table) {
            $table->uuid('srv_id')->primary();
            $table->string('srv_code', 128);
            $table->string('srv_name', 128);
            $table->string('srv_image', 256)->nullable();
            $table->char('srv_active', 1)->default('Y');
            $table->uuid('srv_created_by');
            $table->dateTime('srv_created_on');
            $table->uuid('srv_updated_by')->nullable();
            $table->dateTime('srv_updated_on')->nullable();
            $table->uuid('srv_deleted_by')->nullable();
            $table->dateTime('srv_deleted_on')->nullable();
            $table->string('srv_deleted_reason', 256)->nullable();
            $table->unique('srv_code', 'tbl_srv_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service');
    }
}
