<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


Route::post('/user', [UserController::class, "Register"]);
Route::post('/oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');
Route::middleware('auth:api')->get('/validate', function (Request $request) {
    return $request->user();
});



