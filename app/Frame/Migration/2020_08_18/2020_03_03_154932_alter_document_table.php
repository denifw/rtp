<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document', function (Blueprint $table) {
            $table->string('doc_description', 255)->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DocumentGroupSeeder::class,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DocumentTypeSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
