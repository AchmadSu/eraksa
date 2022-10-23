<?php

use Illuminate\Http\Request;
use App\Models\VerificationCodes;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\AssetsController;
use App\Http\Controllers\API\CategoryAssetsController;
use App\Http\Controllers\API\VerificationCodesController;

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
    Route::post('register', 'register'); 
    Route::post('login', 'login');
    Route::post('logout', 'logout');

    Route::get('users/getAll', 'index');
    Route::get('users/getAllSuperAdmin', 'getSuperAdmin');
    Route::get('users/detail/{id}', 'show');

    Route::put('users/update', 'update');
    Route::delete('users/delete/{id}', 'delete');
    Route::delete('users/deleteMultiple', 'deleteMultiple');

    Route::get('users/trash', 'trash');
    Route::put('users/restore/{id}', 'restore');
    Route::put('users/restoreMultiple', 'restoreMultiple');
});

/**--- Assets --- */
Route::controller(AssetsController::class)->group(function(){
    Route::get('assets/getAll', 'index');
    Route::get('assets/detail/{id}', 'show');
    
    Route::post('assets/create', 'create'); 
    Route::put('assets/update', 'update');
    Route::delete('assets/delete/{id}', 'delete');
    Route::delete('assets/deleteMultiple', 'deleteMultiple');

    Route::get('assets/trash', 'trash');
    Route::put('assets/restore/{id}', 'restore');
    Route::put('assets/restoreMultiple', 'restoreMultiple');
});

/**--- Category Assets --- */
Route::controller(CategoryAssetsController::class)->group(function(){
    Route::get('categoryAssets/getAll', 'index');
    Route::get('categoryAssets/detail/{id}', 'show');
    
    Route::post('categoryAssets/create', 'create'); 
    Route::put('categoryAssets/update', 'update');
    Route::delete('categoryAssets/delete/{id}', 'delete');
    Route::delete('categoryAssets/deleteMultiple', 'deleteMultiple');

    Route::get('categoryAssets/trash', 'trash');
    Route::put('categoryAssets/restore/{id}', 'restore');
    Route::put('categoryAssets/restoreMultiple', 'restoreMultiple');
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
