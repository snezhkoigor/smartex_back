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
	Route::get('/files/{storage}/{filename}/{fit_size?}', function ($storage, $filename, $fit_size = null) {
		if (Storage::disk($storage)->exists($filename)) {
			$image = Image::make(Storage::disk($storage)->get($filename));
			
			if ($fit_size)
			{
				$image->fit($fit_size);
			}

			return $image->response();
		}

		throw new NotFoundHttpException('Image not found');
	});

	Route::get('/logout', 'User\LoginController@logout');
	Route::post('/login', 'User\LoginController@login');
	Route::post('/user/registration', 'User\RegistrationController@registration');
	Route::get('/user/activation/{hash}', 'User\ActivationController@activation');
	Route::post('/user/password/reset', 'User\ResetPasswordController@resetPassword');

	Route::middleware(['auth:api', 'ability:'.Role::ROLE_ADMIN.','])->group(function() {
		//Meta
		Route::get('/meta/wallets', 'WalletController@getFormMeta');
		Route::get('/meta/commissions', 'CommissionController@getFormMeta');

		//News
		Route::get('/news', 'NewsController@getNews');
		Route::get('/news/{news_id}', 'NewsController@getNewsById');
		Route::post('/news', 'NewsController@add');
		Route::put('/news/{news_id}', 'NewsController@updateById');
		Route::delete('/news/{news_id}', 'NewsController@deleteById');

		//Courses
		Route::get('/courses', 'CourseController@getCourses');
		Route::put('/courses/{course_id}', 'CourseController@updateById');

		//Payment_systems
		Route::get('/payment_systems', 'PaymentSystemController@getPaymentSystems');
		Route::get('/payment_systems/{payment_system_id}', 'PaymentSystemController@getPaymentSystemById');
		Route::post('/payment_systems', 'PaymentSystemController@add');
		Route::put('/payment_systems/{payment_system_id}', 'PaymentSystemController@updateById');
		Route::delete('/payment_systems/{payment_system_id}', 'PaymentSystemController@deleteById');

		//Wallets
		Route::get('/wallets/{wallet_id}', 'WalletController@getWalletById');
		Route::get('/wallets', 'WalletController@getWallets');
		Route::post('/wallets/check', 'WalletController@checkAccess');
		Route::post('/wallets', 'WalletController@add');
		Route::put('/wallets/{wallet_id}', 'WalletController@updateById');
		Route::delete('/wallets/{wallet_id}', 'WalletController@deleteById');

		//Commissions
		Route::get('/commissions/{commission_id}', 'CommissionController@getCommissionById');
		Route::get('/commissions', 'CommissionController@getCommissions');
		Route::post('/commissions', 'CommissionController@add');
		Route::put('/commissions/{commission_id}', 'CommissionController@updateById');
		Route::delete('/commissions/{commission_id}', 'CommissionController@deleteById');

		//Log activities
		Route::get('/logs', 'LogActivityController@getLogActivities');
		Route::get('/logs/{log_id}', 'LogActivityController@getLogActivityById');
	});

	Route::middleware(['auth:api', 'ability:'.Role::ROLE_ADMIN.'|'.Role::ROLE_OPERATOR.','])->group(function() {
		//User
		Route::get('/me', 'User\ProfileController@profile');
		Route::put('/me', 'User\ProfileController@updateProfile');
		Route::post('/refresh', 'User\LoginController@refresh');
		Route::get('/users', 'User\UserController@getUsers');
		Route::post('/users', 'User\UserController@add');
		Route::put('/users/{user_id}', 'User\UserController@updateById');
		Route::delete('/users/{user_id}', 'User\UserController@deleteById');
		Route::get('/meta/users', 'User\UserController@getFormMeta');
		Route::get('/users/{user_id}', 'User\UserController@getUserById');

		// Payments
		Route::get('/payments/pdf/transactions/{user_id}', 'PaymentController@pdfTransactionsByUser');
		Route::get('/payments/{payment_id}', 'PaymentController@getPaymentById');
		Route::put('/payments/{payment_id}/confirm', 'PaymentController@confirm');

		//Exchanges
		Route::get('/exchanges', 'ExchangeController@getExchanges');

		//Widgets
		Route::get('/widgets/clients/totalRegistrations', 'WidgetController@totalClientRegistrations');
		Route::get('/widgets/clients/totalRegistrationsAndActivations/{period_type?}', 'WidgetController@totalClientRegistrationsAndActivations');
		Route::get('/widgets/exchanges/totalFinishedExchanges', 'WidgetController@totalFinishedExchages');
		Route::get('/widgets/exchanges/totalNewExchanges', 'WidgetController@totalNewExchages');
		Route::get('/widgets/payments/totalFinishedInPayments', 'WidgetController@totalFinishedInPayments');
		Route::get('/widgets/payments/currenciesInPaymentsByLastMonth', 'WidgetController@currenciesInPaymentsByLastMonth');
		Route::get('/widgets/payments/currenciesOutPaymentsByLastMonth', 'WidgetController@currenciesOutPaymentsByLastMonth');
		Route::get('/widgets/payment_systems/paymentSystemsPaymentsByLastMonth', 'WidgetController@paymentSystemsPaymentsByLastMonth');
		Route::get('/widgets/exchanges/dynamicByLastMonth', 'WidgetController@getExchangesDynamicByLastMonth');
	});
});