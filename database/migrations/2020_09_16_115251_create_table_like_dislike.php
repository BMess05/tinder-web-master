<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLikeDislike extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('like_dislike', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->smallInteger('like_dislike')->nullable()->comment = "1 for like. 0 for dislike";
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
        Schema::dropIfExists('like_dislike');
    }
}
