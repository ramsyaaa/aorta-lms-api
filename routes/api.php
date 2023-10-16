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

Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

Route::group(['middleware' => 'api', 'prefix' => 'v1', 'as' => 'api.',], function () {
    Route::post('authenticate', 'AuthController@authenticate')->name('authenticate');
    Route::post('register', 'AuthController@register')->name('register');
    Route::post('logout', 'AuthController@logout');

    Route::group(['middleware' => ['auth', 'admin', 'verified'], 'prefix' => 'admin', 'as' => 'admin.',], function () {
        Route::get('users', 'Admin\UserController@index')->name('getUsers');
        Route::post('users', 'Admin\UserController@store')->name('storeUsers');
        Route::put('users/{uuid}', 'Admin\UserController@update')->name('updateUsers');
        Route::delete('users/{uuid}', 'Admin\UserController@delete')->name('deleteUsers');
    });
});
