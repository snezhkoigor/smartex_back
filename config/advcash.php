<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Your Account Email
	|--------------------------------------------------------------------------
	|
	| Merchant's account number (email).
	|
	*/

	'account_email' => env('AC_ACCOUNT_EMAIL', 'email@example.com'),


	/*
	|--------------------------------------------------------------------------
	| API Name
	|--------------------------------------------------------------------------
	|
	| API name in Advanced Cash system
	|
	*/

	'api_name' => env('AC_API_NAME', 'api_name'),

	/*
	|--------------------------------------------------------------------------
	| Authentication Token
	|--------------------------------------------------------------------------
	|
	| Generated token at authentication section
	|
	*/

	'auth_token' => env('AC_AUTH_TOKEN', 'api_name'),


];
