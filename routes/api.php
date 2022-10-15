<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\VerificationCodesController;
use App\Models\VerificationCodes;

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

/**--- Users --- */
Route::controller(UsersController::class)->group(function(){
    Route::get('users', 'index');
    Route::get('user/{id}', 'getUserById');
    Route::post('register', 'register'); 
    Route::post('login', 'login');
    Route::post('user/update', 'updateUser');
});

/** --- Verification Codes --- */
Route::controller(VerificationCodesController::class)->group(function(){
    Route::post('checkOtp/{user_id}', 'checkOtp');
    Route::post('/generate/{user_id}', 'generate')->name('generateNewOtp');
    Route::post('/regenerate/{user_id}', 'updateOtp')->name('regenerateOtp');
});

Route::get('sendSMS', [App\Http\Controllers\API\TwilioSMSController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::resource('products', ProductController::class);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
