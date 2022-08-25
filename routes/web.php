<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('email/verify/{token}', 'Admin\UserController@verify_email');
Route::get('logout', 'Auth\LoginController@logout');

Route::group(['middleware' => ['auth', 'checkAccess'] ], function() {
    Route::get('/', 'Admin\DashboardController@dashboard');
    Route::get('dashboard', 'Admin\DashboardController@dashboard')->name('dashboard');
    Route::get('users/location', 'Admin\UserController@userLocation');
    Route::get('interests', 'Admin\DashboardController@list_interests')->name('list_interests');
    Route::get('interests/add', 'Admin\DashboardController@add_interest')->name('add_interest');
    Route::post('interests/save', 'Admin\DashboardController@save_interest')->name('save_interest');
    Route::get('interests/edit/{id}', 'Admin\DashboardController@edit_interest')->name('edit_interest');
    Route::post('interests/update/{id}', 'Admin\DashboardController@update_interest')->name('update_interest');
    Route::get('interests/delete/{id}', 'Admin\DashboardController@delete_interest')->name('delete_interest');

    Route::get('users/reported', 'Admin\UserController@list_reported_users')->name('list_reported_users'); 
    Route::get('users/{filter?}', 'Admin\UserController@listUsers')->name('users');
    Route::get('user/add', 'Admin\UserController@addUser')->name('addUser');
    Route::post('user/save', 'Admin\UserController@saveUser')->name('saveUser');
    Route::get('user/profile/{id}', 'Admin\UserController@userProfile')->name('userProfile');


    
    Route::get('user/block/{id}', 'Admin\UserController@blockUser')->name('blockUser');
    Route::get('user/unblock/{id}', 'Admin\UserController@unblockUser')->name('unblockUser');
    Route::get('user/export_user/{filter?}', 'Admin\UserController@download_user_csv')->name('download_user_csv');
    
    
    Route::get('user/edit/{id}', 'Admin\UserController@editUser')->name('editUser');
    Route::post('user/update/{id}', 'Admin\UserController@updateUser')->name('updateUser');
    Route::get('user/delete/{id}', 'Admin\UserController@deleteUser')->name('deleteUser');
    
    Route::get('reason/list', 'Admin\UserController@listReportReasons')->name('listReportReasons');
    Route::get('reason/add', 'Admin\UserController@addReason')->name('addReason');
    Route::get('reason/edit/{id}', 'Admin\UserController@edit_reason')->name('edit_reason');
    Route::get('reason/delete/{id}', 'Admin\UserController@deleteReason')->name('deleteReason');
    Route::post('reason/save', 'Admin\UserController@saveReason')->name('saveReason');
    Route::post('reason/update/{id}', 'Admin\UserController@updateReason')->name('updateReason');
    Route::get('admins', 'Admin\UserController@list_admins')->name('list_admins');
    Route::get('admin/add', 'Admin\UserController@add_admin')->name('add_admin');
    Route::post('admin/save', 'Admin\UserController@saveAdmin')->name('saveAdmin');
    Route::get('admin/edit/{id}', 'Admin\UserController@editAdmin')->name('editAdmin');


    /**** Subscription plan routes **********/
    Route::group(['prefix' =>'subscription/plan/'], function() {
        Route::get('list', 'Admin\SubscriptionPlansController@index')->name('listSubscriptionPlans');
        Route::get('show/{id}', 'Admin\SubscriptionPlansController@show')->name('showSubscriptionPlan');
        Route::get('create', 'Admin\SubscriptionPlansController@create')->name('createSubscriptionPlan');
        Route::post('store', 'Admin\SubscriptionPlansController@store')->name('storeSubscriptionPlan');
        Route::get('edit/{id}', 'Admin\SubscriptionPlansController@edit')->name('editSubscriptionPlan');
        Route::post('update/{id}', 'Admin\SubscriptionPlansController@update')->name('updateSubscriptionPlan');
        Route::get('destroy/{id}', 'Admin\SubscriptionPlansController@destroy')->name('deleteSubscriptionPlan');
        Route::get('publish/{id}', 'Admin\SubscriptionPlansController@publish')->name('publishSubscriptionPlan');
        Route::get('unpublish/{id}', 'Admin\SubscriptionPlansController@unpublish')->name('unpublishSubscriptionPlan');
    });
    /**** End Subscription Plans routes *****/

    /**** Feedback Listing ****/
    Route::group(['prefix' =>'feedback/'], function() {
        Route::get('list', 'Admin\UserController@userFeedback')->name('userFeedback');
    });

    /**** End ****/
});

Route::group(['middleware' => ['auth', 'checkAdmin'] ], function() {
    Route::get('users/{filter?}', 'Admin\UserController@listUsers')->name('users');
    Route::get('user/add', 'Admin\UserController@addUser')->name('addUser');
    Route::post('user/save', 'Admin\UserController@saveUser')->name('saveUser');
    Route::get('user/profile/{id}', 'Admin\UserController@userProfile')->name('userProfile');

    Route::get('companies', 'Admin\CompanyController@listCompanies')->name('listCompanies');
    Route::get('companies/add', 'Admin\CompanyController@add')->name('addCompany');
    Route::post('companies/save', 'Admin\CompanyController@save')->name('saveCompany');

    Route::get('companies/edit/{id}', 'Admin\CompanyController@edit')->name('editCompany');
    Route::post('companies/update/{id}', 'Admin\CompanyController@update')->name('updateCompany');
    Route::get('companies/delete/{id}', 'Admin\CompanyController@delete')->name('deleteCompany');

    Route::get('plans', 'Admin\CompanyPlanController@listPlans')->name('listPlans');
    Route::get('plans/add', 'Admin\CompanyPlanController@createPlan')->name('create.plan');
    Route::post('plans/save', 'Admin\CompanyPlanController@storePlan')->name('store.plan');
    Route::get('plans/delete/{id}', 'Admin\CompanyPlanController@deletePlan')->name('deletePlan');
    Route::post('plans/change_status', 'Admin\CompanyPlanController@activeInactivePlan')->name('activeInactivePlan');

  
});

Auth::routes(['register' => false]);

Route::get('privacy/{lang}', 'Api\PolicyAndTermsController@policy')->name('privacy');
Route::get('terms/{lang}', 'Api\PolicyAndTermsController@terms')->name('terms');
/*
    Route::get('privacy/{lang}', function($lang) {
        App::setlocale($lang);
        return view('privacy_policy',compact('lang'));
    })->name('privacy');

    Route::get('terms/{lang}', function($lang) {
        App::setlocale($lang);
        return view('terms_of_use',compact('lang'));
    })->name('terms'); 
*/

Route::get('contact/{lang}', function($lang) {
    App::setlocale($lang);
    return view('contact_us',compact('lang'));
})->name('contact');

Route::get('feedback/{lang}', function($lang) {
    App::setlocale($lang);
    return view('feedback',compact('lang'));
})->name('feedback');


Route::post('send_mail', 'Admin\DashboardController@send_mail')->name('send_mail');
// Route::get('/home', 'HomeController@index')->name('home');
Route::group(['prefix' =>'company'], function() {
    Route::get('password/set/{email}', 'Company\AuthController@setPassword')->name('setPassword');
    Route::post('password/set', 'Company\AuthController@setCompanyPassword')->name('setCompanyPassword');
    Route::get('login', 'Company\AuthController@login')->name('companyLogin');
    Route::post('signin', 'Company\AuthController@companySignIn')->name('companySignIn');

    Route::get('forget_password', 'Company\AuthController@forget_password')->name('companyForgetPassword');
    Route::post('reset_password_mail', 'Company\AuthController@passwordResetMail')->name('companyPasswordResetMail');

    Route::get('reset_password', 'Company\AuthController@reset_password')->name('companyResetPassword');
    Route::post('update_password', 'Company\AuthController@update_password')->name('companyUpdatePassword');
    
    
    
    Route::group(['middleware' => ['auth:companies'] ], function() {
        Route::get('/logout', 'Company\AuthController@logout')->name('companyLogout');
        Route::get('/', 'Company\DashboardController@index')->name('companyDashboard');
        Route::get('/advertisements', 'Company\AdvertisementController@index')->name('listAdvertisements');
        Route::get('advertisements/add', 'Company\AdvertisementController@add')->name('addAdvertisement');
        Route::post('advertisements/save', 'Company\AdvertisementController@save')->name('saveAdvertisement');

        Route::get('advertisements/edit/{id}', 'Company\AdvertisementController@edit')->name('editAdvertisement');
        Route::post('advertisements/update/{id}', 'Company\AdvertisementController@update')->name('updateAdvertisement');

        Route::get('advertisements/delete/{id}', 'Company\AdvertisementController@delete')->name('deleteAdvertisement');

        Route::post('advertisements/change_status', 'Company\AdvertisementController@activeInactiveAdd')->name('activeInactiveAdd');

        
        Route::get('subscription/choose', 'Company\CompaySubscriptionController@index')->name('chooseCompanySubscription');
        Route::get('/subscription/show/{plan}', 'Company\CompaySubscriptionController@show')->name('showSubscription');
        Route::post('subscription/save', 'Company\CompaySubscriptionController@create')->name('saveCompanySubscription');

        
    });    
});

Route::post('companies/subscription/webhook', '\App\Http\Controllers\WebhookController@handleWebhook')->name('handleWebhook');
