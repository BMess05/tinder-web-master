<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionFeaturesStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_features_status', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('subscriptions');

            $table->integer('available_boost')->default(0)->nullable();
            $table->dateTime('last_boosted_on')->nullable();

            $table->integer('available_likes')->default(100)->nullable();
            $table->dateTime('last_liked_on')->nullable();

            $table->integer('available_super_likes')->default(0)->nullable();
            $table->dateTime('last_super_liked_on')->nullable();

            $table->integer('available_last_likes')->default(0)->nullable();
            $table->dateTime('last_last_liked_on')->nullable();

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
        Schema::dropIfExists('table_subscription_features_status');
    }
}
