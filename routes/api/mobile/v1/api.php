<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//controller namespace
Route::group(['namespace' => 'Web\V1'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
    });
    
});