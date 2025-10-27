<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'version' => '1.0.0',
        'last_refreshed_at' => Carbon::now('UTC')->format('Y-m-d\TH:i:s\Z'),
        'last_refreshed_by' => 'API',
        'updated_at' => Carbon::now('UTC')->format('Y-m-d\TH:i:s\Z'),
        'message' => 'API is up and running'
    ]);
});

Route::get('/home', 'HomeController@index')->name('home');
