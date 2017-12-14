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
		Route::delete('/news/{news_id}', 'NewsController@changeActiveFieldById');

		// Courses
		Route::get('/courses', 'CourseController@getCourses');
		Route::post('/courses/{course_id}', 'CourseController@updateById');
	});
});