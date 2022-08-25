<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->integer('age_from')->default(0);
            $table->integer('age_to')->default(0);
            $table->string('gender_group')->nullable();
            $table->string('address', 1500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_preferences');
    }
}
