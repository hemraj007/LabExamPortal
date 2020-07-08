<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'API\UserController@login');
Route::post('student_register', 'API\UserController@student_register');
Route::post('admin_register', 'API\UserController@admin_register');
Route::post('/password/email', 'API\ForgotPasswordController@sendResetLinkEmail');
Route::post('/password/reset', 'API\ResetPasswordController@reset');



Route::group(['middleware' => 'auth:api'], function(){
Route::get('fetch_course','API\UserController@fetch_course');
Route::post('startExam','API\ExamController@startExam');
Route::post('logout','API\UserController@logout');
Route::get('details', 'API\UserController@details');

});