<?php

use App\Http\Controllers\MasterData\EmployeeController;
use App\Http\Controllers\Navigator\UserModuleController;
use App\Http\Controllers\Timekeeping\FTPApprovalController;
use App\Http\Controllers\Timekeeping\FTPController;
use App\Http\Controllers\Timekeeping\ManageDTRController;
use App\Http\Controllers\Timekeeping\PayrollPeriodController;
use App\Http\Controllers\TimeKeeping\PayrollPeriodSGController;
use App\Http\Controllers\Timekeeping\RawLogsController;
use App\Http\Controllers\User\UserRightsController;
use App\ScheduledTask\TimeKeeper;
use App\Service\DTRService;
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

Route::get('run',function(){
    $time_keeper = new TimeKeeper();

}); 

Route::middleware('auth:sanctum')->get('/navigator/user-modules', UserModuleController::class);



Route::prefix('master-data')->group(function(){ 
    Route::prefix('employee')->group(function(){ 
        // Route::get('list/{id}',[OtherIncomeController::class,'list']);
        // Route::post('save',[OtherIncomeController::class,'save']);
        Route::get('list',[EmployeeController::class,'list']);
        Route::get('list-by-level/{level}',[EmployeeController::class,'listByLevel']);
        
    });
    
});

    Route::prefix('user-settings')->group(function(){ 
        Route::prefix('user-rights')->group(function(){ 
            Route::get('user-list',[UserRightsController::class,'list']);
            Route::get('user-modules',[UserRightsController::class,'modules_list']);
            Route::post('save-rights',[UserRightsController::class,'saveRights']);
            Route::post('show-rights',[UserRightsController::class,'showRights']);
        });
});

Route::prefix('timekeeping')->group(function(){ 
    Route::prefix('semi-payroll-period')->group(function(){ 
        Route::get('list',[PayrollPeriodController::class,'list']);
        Route::get('index',[PayrollPeriodController::class,'index']);
        Route::post('prepare',[PayrollPeriodController::class,'prepare']);
        Route::get('prepare/{period_id}',[PayrollPeriodController::class,'prepare']);
    });

    Route::prefix('sg-payroll-period')->group(function(){ 
        Route::get('index',[PayrollPeriodSGController::class,'index']);
        Route::post('prepare',[PayrollPeriodSGController::class,'prepare']);
          Route::get('list',[PayrollPeriodSGController::class,'list']);
    });

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

    Route::prefix('manage-dtr-semi')->group(function(){
        Route::get('data/{period_id}/{emp_id}',[ManageDTRController::class,'data']);

        Route::get('draw-logs/{period_id}/{emp_id}',[ManageDTRController::class,'drawLogs']);
        Route::post('draw-logs',[ManageDTRController::class,'drawLogs']);
        

        Route::get('compute-logs/{period_id}/{emp_id}',[ManageDTRController::class,'computeLogs']);
        Route::post('compute-logs',[ManageDTRController::class,'computeLogs']);

        Route::get('fill-out-logs/{period_id}/{emp_id}',[ManageDTRController::class,'fillOutLogs']);
        Route::post('fill-out-logs',[ManageDTRController::class,'fillOutLogs']);
         
        Route::get('complete-logs/{period_id}/{emp_id}',[ManageDTRController::class,'completeLogs']);
        Route::post('complete-logs',[ManageDTRController::class,'completeLogs']);
      
        Route::post('update-logs',[ManageDTRController::class,'updateLog']);

        Route::get('draw-all-logs/{period_id}',[ManageDTRController::class,'drawAllLogs']);
    });

    Route::prefix('logs')->group(function(){
        Route::get('data/{period_id}/{emp_id}',[RawLogsController::class,'data']);
        Route::post('update-log',[RawLogsController::class,'updateLog']);
    });
});
