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
            $table->bigIncrements('dcg_id');
            $table->string('dcg_code', 125);
            $table->string('dcg_description', 255);
            $table->string('dcg_table', 125);
            $table->string('dcg_value_field', 125);
            $table->string('dcg_text_field', 125);
            $table->char('dcg_active', 1)->default('Y');
            $table->bigInteger('dcg_created_by');
            $table->dateTime('dcg_created_on');
            $table->bigInteger('dcg_updated_by')->nullable();
            $table->dateTime('dcg_updated_on')->nullable();
            $table->bigInteger('dcg_deleted_by')->nullable();
            $table->dateTime('dcg_deleted_on')->nullable();
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
