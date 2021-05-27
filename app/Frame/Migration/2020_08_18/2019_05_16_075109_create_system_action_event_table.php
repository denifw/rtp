<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemActionEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_action_event', function (Blueprint $table) {
            $table->bigIncrements('sae_id');
            $table->bigInteger('sae_sac_id')->unsigned();
            $table->foreign('sae_sac_id', 'tbl_sae_sac_id_foreign')->references('sac_id')->on('system_action');
            $table->string('sae_description', 255);
            $table->integer('sae_order');
            $table->char('sae_active', 1)->default('Y');
            $table->bigInteger('sae_created_by');
            $table->dateTime('sae_created_on');
            $table->bigInteger('sae_updated_by')->nullable();
            $table->dateTime('sae_updated_on')->nullable();
            $table->bigInteger('sae_deleted_by')->nullable();
            $table->dateTime('sae_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => SystemActionEventSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_action_event');
    }
}
