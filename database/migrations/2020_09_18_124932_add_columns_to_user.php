<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('city')->nullable();
            $table->string('company')->nullable();
            $table->string('interests')->nullable();
            $table->smallInteger('app_notification')->default(1)->comment = "1 for notification ON and 0 for notification OFF";
            $table->string('email_verification_token')->nullable();
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
            $table->dropColumn(['city', 'company', 'interests', 'app_notification', 'email_verification_token']);
        });
    }
}
