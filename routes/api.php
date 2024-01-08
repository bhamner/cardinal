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


Route::get('/places', function () {
    return App\Models\Place::groupByRaw('concat(long_name,", ",state)')->selectRaw('concat(long_name,", ",state) as citystate')->get();
});

Route::get('/places/long_name_state',function() {
    $place = \Request::get('location');
    return App\Models\Place::where('long_name',trim(explode(',',$place)[0]))->where('state',trim(explode(',',$place)[1]))->firstOrFail();
});



