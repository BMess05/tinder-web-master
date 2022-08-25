<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoumnsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->smallInteger('type')->default(0);
            $table->string('dob')->nullable();
            $table->smallInteger('gender')->nullable()->comment = "1 for Male, 2 for Female";
            $table->string('university')->nullable();
            $table->string('business')->nullable();
            $table->smallInteger('interested_in')->nullable()->comment = "1 for Male, 2 for Female, 3 for Both";
            $table->string('image')->nullable();
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
            $table->dropColumn(['type', 'dob', 'gender', 'university', 'business', 'interested_in', 'image']);
        });
    }
}
