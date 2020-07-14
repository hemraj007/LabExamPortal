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
Route::get('startExam','API\ExamController@startExam');
Route::get('fetch_source_code','API\ExamController@fetch_source_code');
Route::put('save_source','API\ExamController@save_source');
Route::put('update_duration','API\ExamController@update_duration');
Route::post('run','API\ExamController@run');
Route::post('final_submission','API\ExamController@final_submission');
Route::post('logout','API\UserController@logout');
Route::get('details', 'API\UserController@details');
Route::get('admin_data','API\AdminController@admin_data');
Route::get('nextExam','API\AdminController@nextExam');
Route::get('number_of_online_users', 'API\AdminController@number_of_online_users');
Route::get('list_online_users', 'API\AdminController@list_online_users');
Route::post('create_exam', 'API\AdminController@create_exam');
Route::get('list_exam', 'API\AdminController@list_exam');
Route::post('add_question', 'API\AdminController@add_question');
Route::get('view_question', 'API\AdminController@view_question');
Route::post('update_instructor', 'API\AdminController@update_instructor');

});
