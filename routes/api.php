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

Route::group(['middleware' => [\App\Http\Middleware\Cors::class], 'namespace'  => 'Api'], function() {
	Route::get('froala/{filename}', function ($filename) {
		return Image::make(storage_path() . '/' . config('froala_wysiwyg.storage_path') . '/' . $filename)->response();
	});

	Route::post('/login', 'User\JwtAuthenticateController@authenticate');
	Route::get('/logout', 'User\JwtAuthenticateController@logout');

	Route::get('/users', 'User\UserController@getUsers');

	Route::get('/news', 'NewsController@getNews');
	Route::post('/news', 'NewsController@add');
	Route::post('/news/{news_id}', 'NewsController@updateById');
	Route::delete('/news/{news_id}', 'NewsController@changeActiveFieldById');

	Route::group(['prefix' => 'user/password/reset', 'namespace' => 'User'], function () {
		Route::post('/request', 'ResetPasswordController@request');
		Route::get('/{token}', 'ResetPasswordController@change');
	});

	Route::middleware([\App\Http\Middleware\Auth::class, 'ability:'.Role::ROLE_ADMIN.','.Role::ROLE_OPERATOR])->group(function() {
//		Route::get('/news', 'NewsController@getNews');
	});
});