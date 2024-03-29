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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//get or post method then route address with function name
//Route::get('/index','App\Http\Controllers\TestController@index');

//we need to make a folder regarding the name of Api under the app/Http/Controllers 
Route::group(['namespace'=>'Api'],function(){
	Route::any('/login','LoginController@login');//here any means any response method like get, post, delete etc.
	//Route::any('/get_profile','LoginController@get_profile');
	//so before go to the contact function we need to check is he or she a valid user or not
	//to check everytime it goes to the middleware class(CheckUser) and checking header valid or not
	//after this we need to register this middleware in the kernel.php file
	Route::any('/contact','LoginController@contact')->middleware('CheckUser');
	Route::any('/get_rtc_token','AccessTokenController@get_rtc_token')->middleware('CheckUser');
});
