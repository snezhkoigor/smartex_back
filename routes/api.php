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
	Route::get('{storage}/{filename}', function ($storage, $filename) {
		if (Storage::disk($storage)->exists($filename)) {
			return Image::make(Storage::disk($storage)->get($filename))->response();
		}

		throw new NotFoundHttpException('Image not found');
	});
	Route::get('/dictionary', 'JwtAuthenticateController@logout');

	Route::group(['namespace' => 'User'], function () {
		Route::post('/login', 'JwtAuthenticateController@authenticate');
		Route::get('/logout', 'JwtAuthenticateController@logout');
		Route::post('/user/password/reset/request', 'ResetPasswordController@request');
		Route::get('/user/password/reset/{token}', 'ResetPasswordController@change');
	});

	Route::middleware([\App\Http\Middleware\Auth::class, 'ability:'.Role::ROLE_ADMIN.','.Role::ROLE_OPERATOR])->group(function() {
		Route::get('/users', 'User\UserController@getUsers');

		// News
		Route::get('/news', 'NewsController@getNews');
		Route::post('/news', 'NewsController@add');
		Route::post('/news/{news_id}', 'NewsController@updateById');
		Route::delete('/news/{news_id}', 'NewsController@deleteById');

		// Courses
		Route::get('/courses', 'CourseController@getCourses');
		Route::post('/courses/{course_id}', 'CourseController@updateById');

		//Payment_systems
		Route::get('/payment_systems', 'PaymentSystemController@getPaymentSystems');
		Route::post('/payment_systems', 'PaymentSystemController@add');
		Route::post('/payment_systems/{payment_system_id}', 'PaymentSystemController@updateById');
		Route::delete('/payment_systems/{payment_system_id}', 'PaymentSystemController@deleteById');

		// Wallets
		Route::get('/payment_systems/{payment_system_id}/wallets', 'WalletController@getWallets');
		Route::post('/payment_systems/{payment_system_id}/wallets/check', 'WalletController@checkAccess');
		Route::post('/payment_systems/{payment_system_id}/wallets', 'WalletController@add');
		Route::post('/payment_systems/{payment_system_id}/wallets/{wallet_id}', 'WalletController@updateById');
		Route::delete('/payment_systems/{payment_system_id}/wallets/{wallet_id}', 'WalletController@deleteById');
	});
});