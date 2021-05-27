<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipmentStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_status', function (Blueprint $table) {
            $table->bigIncrements('eqs_id');
            $table->string('eqs_name', 255);
            $table->char('eqs_active', 1)->default('Y');
            $table->bigInteger('eqs_created_by');
            $table->dateTime('eqs_created_on');
            $table->bigInteger('eqs_updated_by')->nullable();
            $table->dateTime('eqs_updated_on')->nullable();
            $table->bigInteger('eqs_deleted_by')->nullable();
            $table->dateTime('eqs_deleted_on')->nullable();
            $table->unique('eqs_name', 'tbl_eqs_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => EquipmentStatusSeeder::class
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_status');
    }
}
