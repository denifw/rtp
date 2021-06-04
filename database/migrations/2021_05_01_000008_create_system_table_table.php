<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_table', function (Blueprint $table) {
            $table->uuid('st_id')->primary();
            $table->string('st_name', 256);
            $table->string('st_prefix', 256);
            $table->string('st_path', 256);
            $table->char('st_active', 1)->default('Y');
            $table->uuid('st_created_by');
            $table->dateTime('st_created_on');
            $table->uuid('st_updated_by')->nullable();
            $table->dateTime('st_updated_on')->nullable();
            $table->uuid('st_deleted_by')->nullable();
            $table->dateTime('st_deleted_on')->nullable();
            $table->string('st_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_table');
    }
}
