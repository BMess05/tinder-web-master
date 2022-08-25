<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionssTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('plan_id');
            $table->foreign('plan_id')->references('id')->on('subscription_plans');

            $table->integer('quantity')->default(1);
            $table->string('product_id');
            $table->string('transaction_id');
            $table->string('original_transaction_id')->nullable();
            
            $table->tinyInteger('platform')->comment('1 = Android, 2= IOS');
            $table->string('apple_id')->nullable();
            $table->string('android_id')->nullable();
   
            $table->dateTime('purchase_date')->nullable();
            $table->dateTime('original_purchase_date')->nullable();
            $table->dateTime('expires_date')->nullable();
            $table->boolean('is_trial_period')->default(0);
            $table->boolean('is_in_intro_offer_period')->default(0);
            
            $table->string('web_order_line_item_id')->nullable();

            $table->string('subscription_group_identifier')->nullable();

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
        Schema::dropIfExists('subscriptions');
    }
}
