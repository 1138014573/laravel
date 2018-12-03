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

//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('/','Home\IndexController@index');
Route::get('/index','Home\IndexController@index');
Route::get('/index/index','Home\IndexController@index');
Route::get('/index/captcha','Home\IndexController@captcha');
Route::get('/user/login','Home\UserController@login');
Route::get('/user/register','Home\UserController@register');

Route::group(['middleware' => ['web']], function () {
    // your routes here
});
