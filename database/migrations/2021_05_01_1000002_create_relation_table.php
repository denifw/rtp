<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relation', function (Blueprint $table) {
            $table->uuid('rel_id')->primary();
            $table->uuid('rel_ss_id')->unsigned()->nullable();
            $table->foreign('rel_ss_id', 'tbl_rel_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('rel_number', 64);
            $table->string('rel_name', 256);
            $table->string('rel_short_name', 64);
            $table->string('rel_website', 128)->nullable();
            $table->string('rel_email', 256)->nullable();
            $table->string('rel_phone', 64)->nullable();
            $table->string('rel_vat', 128)->nullable();
            $table->uuid('rel_of_id')->nullable();
            $table->uuid('rel_cp_id')->nullable();
            $table->json('rel_remark')->nullable();
            $table->char('rel_active', 1)->default('Y');
            $table->uuid('rel_created_by');
            $table->dateTime('rel_created_on');
            $table->uuid('rel_updated_by')->nullable();
            $table->dateTime('rel_updated_on')->nullable();
            $table->uuid('rel_deleted_by')->nullable();
            $table->dateTime('rel_deleted_on')->nullable();
            $table->string('rel_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relation');
    }
}
