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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::get('q_sms/{phone}/{text}', 'WorkController@queue_user_sms');
Route::get('create_slags', 'WorkController@create_slags_for_numbers');
Route::get('participate/{slag}', 'WorkController@reconcile_slag_click');
Route::get('works', 'WorkController@working');
