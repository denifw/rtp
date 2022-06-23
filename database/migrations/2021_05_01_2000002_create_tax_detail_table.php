<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_detail', function (Blueprint $table) {
            $table->uuid('td_id')->primary();
            $table->uuid('td_tax_id')->unsigned();
            $table->foreign('td_tax_id', 'tbl_td_tax_id_fkey')->references('tax_id')->on('tax');
            $table->uuid('td_child_tax_id')->unsigned();
            $table->foreign('td_child_tax_id', 'tbl_td_child_tax_id_fkey')->references('tax_id')->on('tax');
            $table->uuid('td_created_by');
            $table->dateTime('td_created_on');
            $table->uuid('td_updated_by')->nullable();
            $table->dateTime('td_updated_on')->nullable();
            $table->uuid('td_deleted_by')->nullable();
            $table->dateTime('td_deleted_on')->nullable();
            $table->string('td_deleted_reason', 256)->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => TaxDetailSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_detail');
    }
}
