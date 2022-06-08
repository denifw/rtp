<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_group', function (Blueprint $table) {
            $table->uuid('dcg_id')->primary();
            $table->string('dcg_code', 128);
            $table->string('dcg_description', 256);
            $table->string('dcg_table', 128);
            $table->string('dcg_value_field', 128);
            $table->string('dcg_text_field', 128);
            $table->char('dcg_active', 1)->default('Y');
            $table->uuid('dcg_created_by');
            $table->dateTime('dcg_created_on');
            $table->uuid('dcg_updated_by')->nullable();
            $table->dateTime('dcg_updated_on')->nullable();
            $table->uuid('dcg_deleted_by')->nullable();
            $table->dateTime('dcg_deleted_on')->nullable();
            $table->string('dcg_deleted_reason', 256)->nullable();
            $table->unique('dcg_code', 'tbl_dcg_code_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DocumentGroupSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_group');
    }
}
