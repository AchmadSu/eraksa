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
use App\Http\Controllers\API\LoansController;
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
            Route::delete('users/delete', 'delete');
            Route::get('users/trash', 'trash');
            Route::put('users/restore', 'restore');
        });
        Route::get('users/detail/{id}', 'show');
        Route::put('users/update', 'update');
        Route::post('resetPhone/{id}', 'resetPhone');
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
            Route::get('assets/trash', 'trash');
            Route::get('assets/percentage', 'percentage');
            Route::post('assets/create', 'create'); 
            Route::put('assets/update', 'update');
            Route::delete('assets/delete', 'delete');
            Route::put('assets/restore', 'restore');
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
            Route::delete('categoryAssets/delete', 'delete');
            Route::get('categoryAssets/trash', 'trash');
            Route::put('categoryAssets/restore', 'restore');
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
            Route::delete('placements/delete', 'delete');
            Route::get('placements/trash', 'trash');
            Route::put('placements/restore', 'restore');
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
            Route::delete('workshops/delete', 'delete');
            Route::get('workshops/trash', 'trash');
            Route::put('workshops/restore', 'restore');
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
                Route::delete('studyPrograms/delete', 'delete');
                Route::get('studyPrograms/trash', 'trash');
                Route::put('studyPrograms/restore', 'restore');
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

/**--- Loans --- */
Route::controller(LoansController::class)->group(function(){
    // dd(Auth::guest());
    Route::middleware('auth:sanctum')->group(function(){
        Route::middleware('role:Super-Admin')->group(function(){
            Route::put('loans/confirmation', 'confirmation');
        });
        Route::put('loans/update', 'update');
        Route::delete('loans/delete', 'delete');
        Route::get('loans/trash', 'trash');
        Route::put('loans/restore', 'restore');
        Route::get('loans/getAll', 'index');
        Route::post('loans/create', 'create'); 
        Route::get('loans/detail/{id}', 'show');
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