<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => ['api', 'language'], 'prefix' => 'auth'], function ($router) {
    Route::post('login', 'Api\AuthController@login');
    Route::post('logout', 'Api\AuthController@logout');
    // Route::post('refresh', 'Api\AuthController@refresh');
    Route::post('register', 'Api\RegisterController@register');
    Route::post('forgot_password', 'Api\ForgotPasswordController@forgot_password');
    Route::post('update_password', 'Api\ForgotPasswordController@update_password');

    Route::get('deleteUser', 'Api\RegisterController@deleteUser');
});

Route::group(['middleware' => ['jwt.verify', 'language']], function ($router) {
    Route::post('upload_image', 'Api\UserController@upload_image');
    Route::post('update_profile', 'Api\UserController@update_profile');
    Route::post('update_language', 'Api\UserController@update_language');
    Route::post('search_users', 'Api\UserController@searchUsers');
    // Route::post('like_dislike', 'Api\UserController@like_dislike');
    Route::get('get_profile/{id}', 'Api\UserController@get_profile');
    Route::get('messages/unread/total', 'Api\UserController@totalUnread');
    Route::post('report_user', 'Api\UserController@report_user');
    Route::get('online_status/{id}', 'Api\UserController@set_online_status');

    Route::get('my_conversations', 'Api\UserController@getAllConversations');
    Route::get('set_show_my_gender/{id}', 'Api\UserController@set_show_my_gender');
    Route::post('upload_chat_image', 'Api\UserController@upload_chat_image');
    Route::post('save_message', 'Api\UserController@save_message');

    Route::post('messages/read', 'Api\UserController@setMessagesRead');

    Route::get('get_chat/{receiver_id}', 'Api\UserController@get_chat');
    Route::get('set_app_notification/{value}', 'Api\UserController@set_app_notification');
    Route::get('account/delete', 'Api\UserController@deleteAccount');
    Route::post('interest/save', 'Api\UserController@save_interest');
    Route::get('all_interests', 'Api\UserController@all_interests');

    Route::post('verify/email', 'Api\UserController@verifyEmailWithOtp');
    Route::get('resend/otp', 'Api\UserController@resendOtp');


    /**** Start Features Routes ****/
    // Route::group([
    // 'middleware' => [ 'feature_status' ],
    // 'prefix' => 'feature'], function ($router) {
    Route::group(['prefix' => 'feature'], function ($router) {
        Route::post('like_dislike', 'Api\SubscriptionFeaturesStatusController@likeDislikeSuperlike');
        Route::get('top_ten_likes', 'Api\UserController@get_who_super_like_me');
        Route::get('get_who_like_me', 'Api\UserController@get_who_like_me');
        Route::get('get_my_matches', 'Api\UserController@get_my_matches');
        Route::get('unmatch/{id}', 'Api\SubscriptionFeaturesStatusController@unmatch');
        Route::get('get_top_picks', 'Api\SubscriptionFeaturesStatusController@getTopPicks');
        Route::post('like_dislike__top_picks', 'Api\SubscriptionFeaturesStatusController@likeDislikeSuperlike_top_picked');
        Route::get('boost', 'Api\SubscriptionFeaturesStatusController@boost');
    });
    /**** End Features Routes ****/


    /****  Start Subscription routes ****/
    Route::group(['prefix' => 'subscription'], function ($router) {
        Route::get('plans/available', 'Api\UserController@getAllAvailablePlansList');
        Route::post('verify/receipt', 'Api\SubscriptionsController@verifyReceiptAddSubscription');
        Route::post('verify', 'Api\SubscriptionsController@checkCurrentSubscriptionStatus');
    });
    /****  End Subscription routes ****/

    /****  Start Android Subscription routes ****/
    Route::group(['prefix' => 'android-subscription'], function ($router) {
        Route::post('add', 'Api\SubscriptionsController@androidAddSubscription');
        Route::post('check', 'Api\SubscriptionsController@androidCheckCurrentSubscriptionStatus');
    });
    /****  End Android Subscription routes ****/

    /****  Start Subscription routes ****/
    Route::group(['prefix' => 'consumable'], function ($router) {
        Route::post('boost', 'Api\ConsumableFeaturesController@addBoost');
    });
    /****  End Subscription routes ****/

    /**** Start Settings routes ****/
    Route::group(['prefix' => 'settings'], function ($router) {
        Route::post('location/virtual/update', 'Api\UserController@virtualLocation');
        Route::post('feedback/add', 'Api\UserController@addFeedback');
    });
    /**** End Settings routes ****/


    /******Reset user features count*********/
    Route::get('feature/reset/counts', 'Api\SubscriptionFeaturesStatusController@resetFeatureCounts');
    /******End Reset user features count*****/

    Route::get('ads', 'Api\AdvertisementController@listAdds');
    Route::post('ads/seen', 'Api\AdvertisementController@ad_seen');
});
