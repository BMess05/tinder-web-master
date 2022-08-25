<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsumableFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumable_features', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');            

            $table->string('consumable_type');
            $table->integer('consumable_quantity')->default(0);
            $table->tinyInteger('platform')->comment('1 = Android, 2= IOS');
            $table->string('apple_id')->nullable();
            $table->string('android_id')->nullable();
            $table->boolean('is_active')->default(1);

            $table->softDeletes();
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
        Schema::dropIfExists('consumable_features');
    }
}
