<?php

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

use \App\Models\Role;
use Intervention\Image\ImageManagerStatic as Image;
use \Illuminate\Support\Facades\Storage;
use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

Route::group(['middleware' => [\App\Http\Middleware\Cors::class], 'namespace'  => 'Api'], function() {
	Route::get('/files/{storage}/{filename}', function ($storage, $filename) {
		if (Storage::disk($storage)->exists($filename)) {
			return Image::make(Storage::disk($storage)->get($filename))->response();
		}

		throw new NotFoundHttpException('Image not found');
	});

	Route::get('/logout', 'User\JwtAuthenticateController@logout');
	Route::post('/login', 'User\JwtAuthenticateController@authenticate');
	Route::post('/user/password/reset', 'User\ResetPasswordController@resetPassword');

	Route::middleware([\App\Http\Middleware\Auth::class, 'ability:'.Role::ROLE_ADMIN])->group(function() {
		//Meta
		Route::get('/meta/wallets', 'WalletController@getFormMeta');
		Route::get('/meta/commissions', 'CommissionController@getFormMeta');

		//News
		Route::get('/news', 'NewsController@getNews');
		Route::get('/news/{news_id}', 'NewsController@getNewsById');
		Route::post('/news', 'NewsController@add');
		Route::post('/news/{news_id}', 'NewsController@updateById');
		Route::delete('/news/{news_id}', 'NewsController@deleteById');

		//Courses
		Route::get('/courses', 'CourseController@getCourses');
		Route::post('/courses/{course_id}', 'CourseController@updateById');

		//Payment_systems
		Route::get('/payment_systems', 'PaymentSystemController@getPaymentSystems');
		Route::get('/payment_systems/{payment_system_id}', 'PaymentSystemController@getPaymentSystemById');
		Route::post('/payment_systems', 'PaymentSystemController@add');
		Route::post('/payment_systems/{payment_system_id}', 'PaymentSystemController@updateById');
		Route::delete('/payment_systems/{payment_system_id}', 'PaymentSystemController@deleteById');

		//Wallets
		Route::get('/wallets/{wallet_id}', 'WalletController@getWalletById');
		Route::get('/wallets', 'WalletController@getWallets');
		Route::post('/wallets/check', 'WalletController@checkAccess');
		Route::post('/wallets', 'WalletController@add');
		Route::post('/wallets/{wallet_id}', 'WalletController@updateById');
		Route::delete('/wallets/{wallet_id}', 'WalletController@deleteById');

		//Commissions
		Route::get('/commissions/{commission_id}', 'CommissionController@getCommissionById');
		Route::get('/commissions', 'CommissionController@getCommissions');
		Route::post('/commissions', 'CommissionController@add');
		Route::post('/commissions/{commission_id}', 'CommissionController@updateById');
		Route::delete('/commissions/{commission_id}', 'CommissionController@deleteById');

		//Log activities
		Route::get('/logs', 'LogActivityController@getLogActivities');
		Route::get('/logs/{log_id}', 'LogActivityController@getLogActivityById');
	});

	Route::middleware([\App\Http\Middleware\Auth::class, 'ability:'.Role::ROLE_ADMIN.','.Role::ROLE_OPERATOR])->group(function() {
		//User
		Route::get('/me', 'User\UserController@profile');
		Route::post('/me', 'User\UserController@updateProfile');

		//Widgets
		Route::get('/widgets/clients/totalRegistrations', 'WidgetController@totalClientRegistrations');
		Route::get('/widgets/clients/totalRegistrationsAndActivations/{period_type?}', 'WidgetController@totalClientRegistrationsAndActivations');
	});
});