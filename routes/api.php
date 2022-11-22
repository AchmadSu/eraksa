<?php

// use Request;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use App\Mail\ResetPasswordLink;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\AssetsController;
use App\Http\Controllers\API\WorkshopsController;
use App\Http\Controllers\API\PlacementsController;
use App\Http\Controllers\API\StudyProgramsController;
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
    Route::middleware(['auth:sanctum'])->group(function(){
        Route::middleware(['role:Super-Admin|Admin'])->group(function(){
            Route::get('users/getAll', 'index')->name('all users');
            Route::get('users/getAllSuperAdmin', 'getSuperAdmin');
            Route::delete('users/delete/{id}', 'delete');
            Route::delete('users/deleteMultiple', 'deleteMultiple');
            Route::get('users/trash', 'trash');
            Route::put('users/restore/{id}', 'restore');
            Route::put('users/restoreMultiple', 'restoreMultiple');
        });
        Route::get('users/detail/{id}', 'show');
        Route::put('users/update', 'update');
        Route::post('logout', 'logout');
    });
    Route::post('register', 'register');
    Route::post('requestResetPassword', 'requestResetPassword');
    Route::post('resetPassword', 'resetPassword'); 
    Route::post('login', 'login');
});

/**--- Assets --- */
Route::controller(AssetsController::class)->group(function(){
    // dd(Auth::guest());
    Route::middleware('auth:sanctum')->group(function(){
        Route::middleware('role:Super-Admin|Admin')->group(function(){
            Route::post('assets/create', 'create'); 
            Route::put('assets/update', 'update');
            Route::delete('assets/delete/{id}', 'delete');
            Route::delete('assets/deleteMultiple', 'deleteMultiple');
            Route::get('assets/trash', 'trash');
            Route::put('assets/restore/{id}', 'restore');
            Route::put('assets/restoreMultiple', 'restoreMultiple');
        });
        Route::get('assets/getAll', 'index');
        Route::get('assets/detail/{id}', 'show');
    });
});

/**--- Category Assets --- */
Route::controller(CategoryAssetsController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::middleware('role:Super-Admin|Admin')->group(function(){
            Route::post('categoryAssets/create', 'create'); 
            Route::put('categoryAssets/update', 'update');
            Route::delete('categoryAssets/delete/{id}', 'delete');
            Route::delete('categoryAssets/deleteMultiple', 'deleteMultiple');
            Route::get('categoryAssets/trash', 'trash');
            Route::put('categoryAssets/restore/{id}', 'restore');
            Route::put('categoryAssets/restoreMultiple', 'restoreMultiple');
        });
        Route::get('categoryAssets/getAll', 'index');
        Route::get('categoryAssets/detail/{id}', 'show');
    });
});

/**--- Placement --- */
Route::controller(PlacementsController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::middleware('role:Super-Admin|Admin')->group(function(){
            Route::post('placements/create', 'create'); 
            Route::put('placements/update', 'update');
            Route::delete('placements/delete/{id}', 'delete');
            Route::delete('placements/deleteMultiple', 'deleteMultiple');
            Route::get('placements/trash', 'trash');
            Route::put('placements/restore/{id}', 'restore');
            Route::put('placements/restoreMultiple', 'restoreMultiple');
        });
        Route::get('placements/getAll', 'index');
        Route::get('placements/detail/{id}', 'show');
    });
});

/**--- Workshops --- */
Route::controller(WorkshopsController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::middleware('role:Super-Admin|Admin')->group(function(){
            Route::get('workshops/getAll', 'index');
            Route::get('workshops/detail/{id}', 'show');
            Route::post('workshops/create', 'create'); 
            Route::put('workshops/update', 'update');
            Route::delete('workshops/delete/{id}', 'delete');
            Route::delete('workshops/deleteMultiple', 'deleteMultiple');
            Route::get('workshops/trash', 'trash');
            Route::put('workshops/restore/{id}', 'restore');
            Route::put('workshops/restoreMultiple', 'restoreMultiple');
        });
    });
});

/**--- Programs Study --- */
Route::controller(StudyProgramsController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::middleware('role:Super-Admin|Admin')->group(function(){
            Route::middleware('role:Super-Admin')->group(function(){
                Route::post('studyPrograms/create', 'create'); 
                Route::put('studyPrograms/update', 'update');
                Route::delete('studyPrograms/delete/{id}', 'delete');
                Route::delete('studyPrograms/deleteMultiple', 'deleteMultiple');
                Route::get('studyPrograms/trash', 'trash');
                Route::put('studyPrograms/restore/{id}', 'restore');
                Route::put('studyPrograms/restoreMultiple', 'restoreMultiple');
            });
            Route::get('studyPrograms/getAll', 'index');
            Route::get('studyPrograms/detail/{id}', 'show');
        });
    });
});

/** --- Verification Codes --- */
Route::controller(VerificationCodesController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::post('verification/check/{user_id}', 'checkOtp');
        Route::post('verification/generate/{user_id}', 'generate')->name('generateNewOtp');
        Route::post('verification/regenerate/{user_id}', 'regenerate')->name('regenerateOtp');
    });
});

// Route::get('sendSMS', [App\Http\Controllers\API\TwilioSMSController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    // Route::resource('products', ProductController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UsersController::class);
    // Route::resource()
});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });