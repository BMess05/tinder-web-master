<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->smallInteger('show_my_gender')->default(0);
            $table->smallInteger('is_premium')->default(0);
            $table->smallInteger('is_blocked')->default(0);
            $table->smallInteger('is_online')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['show_my_gender', 'is_premium', 'is_blocked', 'is_online']);
        });
    }
}
