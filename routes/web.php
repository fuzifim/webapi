<?php

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
Route::get('/', array(
	'as' => 'register',
	'uses' => 'UserController@register')); 
Route::post('api/register', array(
	'as' => 'register.request',
	'uses' => 'UserController@registerRequest')); 
Route::get('api/login', array(
	'as' => 'login',
	'uses' => 'UserController@login')); 
Route::post('api/login', array(
	'as' => 'login.request',
	'uses' => 'UserController@loginRequest')); 	
Route::get('api/user-info', array(
	'as' => 'user.info',
	'uses' => 'UserController@userInfo')); 
	
Route::group(['middleware' => 'jwt.auth'], function () {
	Route::post('api/user-info', array(
		'as' => 'user.info.request',
		'uses' => 'UserController@userInfoRequest')); 
	Route::post('api/user-update', array(
		'as' => 'user.update.request',
		'uses' => 'UserController@userUpdateRequest')); 
});