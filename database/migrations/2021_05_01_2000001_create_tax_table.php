<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax', function (Blueprint $table) {
            $table->uuid('tax_id')->primary();
            $table->uuid('tax_ss_id')->unsigned();
            $table->foreign('tax_ss_id', 'tbl_tax_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('tax_name', 128);
            $table->float('tax_percent')->nullable();
            $table->char('tax_active', 1)->default('Y');
            $table->uuid('tax_created_by');
            $table->dateTime('tax_created_on');
            $table->uuid('tax_updated_by')->nullable();
            $table->dateTime('tax_updated_on')->nullable();
            $table->uuid('tax_deleted_by')->nullable();
            $table->dateTime('tax_deleted_on')->nullable();
            $table->string('tax_deleted_reason', 256)->nullable();
            $table->unique(['tax_ss_id', 'tax_name'], 'tbl_tax_ss_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax');
    }
}
