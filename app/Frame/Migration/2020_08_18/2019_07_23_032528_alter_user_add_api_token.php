<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserAddApiToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_mapping', function (Blueprint $table) {
            $table->dropColumn('ump_api_token');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('us_api_token', 32)->nullable();
        });
        $query = 'SELECT us_id, us_username
                FROM users';
        $sqlResult = \Illuminate\Support\Facades\DB::select($query);
        if (empty($sqlResult) === false) {
            foreach ($sqlResult as $row) {
                \Illuminate\Support\Facades\DB::table('users')->where('us_id', $row->us_id)->update([
                    'us_api_token' => md5($row->us_username)
                ]);
            }
        }
        Schema::table('users', function (Blueprint $table) {
            $table->string('us_api_token', 32)->nullable(false)->change();
        });
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
