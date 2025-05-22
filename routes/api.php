<?php

use App\Http\Controllers\MasterData\EmployeeController;
use App\Http\Controllers\Timekeeping\FTPApprovalController;
use App\Http\Controllers\Timekeeping\FTPController;
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


Route::prefix('master-data')->group(function(){ 
    Route::prefix('employee')->group(function(){ 
        // Route::get('list/{id}',[OtherIncomeController::class,'list']);
        // Route::post('save',[OtherIncomeController::class,'save']);
        Route::get('list',[EmployeeController::class,'list']);
        Route::get('list-by-level/{level}',[EmployeeController::class,'listByLevel']);
        
    });
    
});

Route::prefix('timekeeping')->group(function(){ 
    Route::prefix('ftp')->group(function(){ 
        Route::get('list/{emp_id?}',[FTPController::class,'list']);

        Route::post('store',[FTPController::class,'store']);
        // Route::post('edit',[FTPController::class,'edit']);
    });

     Route::prefix('ftp-approval')->group(function(){ 
        Route::get('list/{emp_id?}',[FTPApprovalController::class,'list']);

        Route::post('approve',[FTPApprovalController::class,'approve']);
        // Route::post('edit',[FTPController::class,'edit']);
    });
});
