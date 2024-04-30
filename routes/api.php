<?php

use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\SchoolController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource("school",SchoolController::class);

Route::post("school-excel",[SchoolController::class,"store_excel"]);

Route::post("receive-provinces",[ProvinceController::class,"fetchAndSaveProvinces"]);

Route::get("province",[ProvinceController::class,"index"]);