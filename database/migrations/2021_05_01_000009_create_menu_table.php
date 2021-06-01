<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->uuid('mn_id')->primary();
            $table->string('mn_name', 128);
            $table->string('mn_code', 128);
            $table->uuid('mn_parent')->nullable();
            $table->integer('mn_order');
            $table->string('mn_icon', '125');
            $table->char('mn_active', 1)->default('Y');
            $table->uuid('mn_created_by');
            $table->dateTime('mn_created_on');
            $table->uuid('mn_updated_by')->nullable();
            $table->dateTime('mn_updated_on')->nullable();
            $table->uuid('mn_deleted_by')->nullable();
            $table->dateTime('mn_deleted_on')->nullable();
            $table->string('mn_deleted_reason', 256)->nullable();
            $table->unique(['mn_code', 'mn_parent'], 'tbl_mn_code_parent_unique');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu');
    }
}
