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
            $table->bigIncrements('tax_id');
            $table->string('tax_code', 50);
            $table->float('tax_percent');
            $table->char('tax_active', 1)->default('Y');
            $table->bigInteger('tax_created_by');
            $table->dateTime('tax_created_on');
            $table->bigInteger('tax_updated_by')->nullable();
            $table->dateTime('tax_updated_on')->nullable();
            $table->bigInteger('tax_deleted_by')->nullable();
            $table->dateTime('tax_deleted_on')->nullable();
            $table->unique('tax_code', 'tbl_tax_code_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => TaxSeeder::class,
        ]);
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
