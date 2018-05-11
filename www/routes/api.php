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
	Route::post('/user/email-check-unique', 'User\RegistrationController@checkEmailUnique');
	Route::post('/user/registration', 'User\RegistrationController@registration');
	Route::get('/user/activation/{hash}', 'User\ActivationController@activation');
	Route::post('/user/password/reset', 'User\ResetPasswordController@resetPassword');
	Route::get('/news/view', 'NewsController@view');
	Route::get('/news/show/{news_id}', 'NewsController@show');
	Route::get('/commissions/view', 'CommissionController@view');
	Route::get('/payment-systems/to', 'PaymentSystemController@getPaymentSystemsTo');
	Route::get('/payment-systems/from', 'PaymentSystemController@getPaymentSystemsFrom');
	Route::post('/exchanges/add', 'ExchangeController@add');
	Route::post('/user/exchanges/can', 'ExchangeController@canExecuteCurrentUser');
	Route::post('/sci/payment/{ps_code}', 'PaymentController@Sci');
	Route::get('/user/not-auth/exchanges/{hash?}', 'ExchangeController@notAuthUserExchange');
	Route::put('/user/exchanges/{exchange_id}/income/confirm', 'PaymentController@manualConfirmIncomeByUser');

	Route::middleware(['auth:api', 'ability:'.Role::ROLE_USER.','])->group(function() {
		Route::put('/user/exchanges/{exchange_id}/comment', 'ExchangeController@saveComment');
	});

	Route::middleware(['auth:api', 'ability:'.Role::ROLE_ADMIN.'|'.Role::ROLE_OPERATOR.','])->group(function() {
		Route::put('/user/exchanges/{exchange_id}/moderation', 'ExchangeController@moderateComment');
	});

	Route::middleware(['auth:api', 'ability:'.Role::ROLE_ADMIN.'|'.Role::ROLE_USER.','])->group(function() {
		Route::get('/user/referrers', 'User\ProfileController@referrers');
		Route::get('/user/payments', 'PaymentController@userPayments');
		Route::get('/user/exchanges', 'ExchangeController@userExchanges');

		Route::put('/user/documents/card', 'User\ProfileController@uploadIdCard');
		Route::put('/user/documents/kyc', 'User\ProfileController@uploadKyc');
		Route::put('/user/password', 'User\ProfileController@updatePassword');
		Route::get('/user/login-logs', 'User\ProfileController@loginLogs');
		Route::put('/user/login-logs/{id}/token-revoke', 'User\ProfileController@tokenRevoke');
	});

	Route::middleware(['auth:api', 'ability:'.Role::ROLE_ADMIN.','])->group(function() {
		//Meta
		Route::get('/meta/wallets', 'WalletController@getFormMeta');
		Route::get('/meta/commissions', 'CommissionController@getFormMeta');
		Route::get('/meta/news', 'NewsController@getFormMeta');

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

	Route::middleware(['auth:api', 'ability:'.Role::ROLE_ADMIN.'|'.Role::ROLE_OPERATOR.'|'.Role::ROLE_USER.','])->group(function() {
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
		Route::post('/user/phone/verification/{country_code}/{phone}', function ($country_code, $phone) {
			if ($country_code && $phone) {
				$post = [
				    'via' => 'sms',
				    'phone_number' => $phone,
				    'country_code' => $country_code,
				    'code_length' => '4',
				    'locale' => 'en'
				];
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.authy.com/protected/json/phones/verification/start');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch,CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Authy-API-Key: CySpG3L9TtpqlpTMfeIcomABoXbuCX88']);
				$response = curl_exec($ch);
				$response = json_decode($response, true);
	
				return response()->json([
					'data' => $response
				], 200);
			}

			throw new NotFoundHttpException('Not found credits from request');
		});
		Route::get('/user/phone/verification/{country_code}/{phone}/{code}', function ($country_code, $phone, $code) {
			if ($country_code && $phone && $code) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.authy.com/protected/json/phones/verification/check?phone_number='.$phone.'&country_code='.$country_code.'&verification_code='.$code);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Authy-API-Key: CySpG3L9TtpqlpTMfeIcomABoXbuCX88']);
				$response = curl_exec($ch);
				$response = json_decode($response, true);
	
				return response()->json([
					'data' => $response
				], 200);
			}

			throw new NotFoundHttpException('Not found credits from request');
		});

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