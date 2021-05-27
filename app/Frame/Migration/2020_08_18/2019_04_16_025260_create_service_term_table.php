<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTermTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_term', function (Blueprint $table) {
            $table->bigIncrements('srt_id');
            $table->string('srt_name', 125);
            $table->string('srt_description', 255)->nullable();
            $table->string('srt_image', 125)->nullable();
            $table->string('srt_color', 125)->nullable();
            $table->string('srt_route', 125);
            $table->integer('srt_order')->nullable();
            $table->bigInteger('srt_srv_id')->unsigned();
            $table->foreign('srt_srv_id', 'tbl_srt_srv_id_foreign')->references('srv_id')->on('service');
            $table->char('srt_trucking', 1)->default('N');
            $table->char('srt_container', 1)->default('N');
            $table->char('srt_forwarding', 1)->default('N');
            $table->char('srt_multi_trucking', 1)->default('N');
            $table->char('srt_warehouse', 1)->default('N');
            $table->char('srt_clearance', 1)->default('N');
            $table->char('srt_transhipment', 1)->default('N');
            $table->char('srt_courier', 1)->default('N');
            $table->char('srt_active', 1)->default('Y');
            $table->bigInteger('srt_created_by');
            $table->dateTime('srt_created_on');
            $table->bigInteger('srt_updated_by')->nullable();
            $table->dateTime('srt_updated_on')->nullable();
            $table->bigInteger('srt_deleted_by')->nullable();
            $table->dateTime('srt_deleted_on')->nullable();
            $table->unique(['srt_srv_id', 'srt_name'], 'tbl_srt_srv_id_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => ServiceTermSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_term');
    }
}
