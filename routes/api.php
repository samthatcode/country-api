<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use App\Http\Controllers\CountryController;

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


Route::prefix('v1')->group(function () {
    Route::prefix('countries')->group(function () {
        Route::match(['get', 'post'], 'refresh', [CountryController::class, 'refresh']);
        Route::get('', [CountryController::class, 'index']);
        Route::get('status', [CountryController::class, 'status']);
        Route::get('image', [CountryController::class, 'image']);
        Route::get('{name}', [CountryController::class, 'show']);
        Route::delete('{name}', [CountryController::class, 'destroy']);
    });
});


require __DIR__ . '/web.php';
