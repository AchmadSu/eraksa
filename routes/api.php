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
    Route::get('users/getAll', 'index');
    Route::get('users/getAllSuperAdmin', 'getSuperAdmin');
    Route::get('users/detail/{id}', 'show');
    Route::post('register', 'register'); 
    Route::post('login', 'login');
    Route::put('users/update', 'update');
    Route::delete('users/{id}', 'delete');
    Route::post('logout', 'logout');

    Route::get('users/trash', 'trash');
    Route::put('users/restore/{id}', 'restore');
});

/** --- Verification Codes --- */
Route::controller(VerificationCodesController::class)->group(function(){
    Route::post('verification/check/{user_id}', 'checkOtp');
    Route::post('verification/generate/{user_id}', 'generate')->name('generateNewOtp');
    Route::post('verification/regenerate/{user_id}', 'regenerate')->name('regenerateOtp');
});

// Route::get('sendSMS', [App\Http\Controllers\API\TwilioSMSController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    // Route::resource('products', ProductController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UsersController::class);
    // Route::resource()
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
