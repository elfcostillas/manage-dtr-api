<?php

namespace App\Http\Controllers\Timekeeping;

use App\Http\Controllers\Controller;
use App\Repository\DTRRepository;
use App\Service\DTRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManageDTRController extends Controller
{
    //

    public function __construct(public DTRService $dtr_service,public DTRRepository $dtr_repo)
    {
        
    }

    public function data(Request $request)
    {
        // dd((int) $request->period_id,(int) $request->emp_id);
        $array = collect(['regular','restday','special_hol','legal_hol','dbl_special','dbl_legal']);
        $result = $this->dtr_service->handleGetDTR((int) $request->period_id,(int) $request->emp_id);
        
        return $this->jsonResponse($result,null,'success');

    }

    public function drawLogs(Request $request)
    {
        // dd($request->emp_id,$request->period_id);
        $result = $this->dtr_service->handleDrawRequest($request->emp_id,$request->period_id);
        return response()->json($result);
        // return $this->jsonResponse($result,null,'success');
    }

    public function computeLogs(Request $request)
    {
        $result = $this->dtr_service->handleComputeRequest($request->emp_id,$request->period_id);
        return $this->jsonResponse($result,null,'success');
    }

    public function fillOutLogs(Request $request)
    {
        $result = $this->dtr_service->handeFillOutLogs($request->emp_id,$request->period_id);
        
        return $this->jsonResponse($result,null,'success');
    }

    public function updateLog(Request $request)
    {
        $result = $this->dtr_service->handleUpdateRequest($request->all());
        return $this->jsonResponse($result,null,'success');
    }

    public function completeLogs(Request $request)
    {
        $result = $this->dtr_service->handeCompletingLogs($request->emp_id,$request->period_id);
        return $this->jsonResponse($result,null,'success');
    }

    public function clearLogs(Request $request)
    {
        $result = $this->dtr_service->handeClearingLogs($request->emp_id,$request->period_id);
        return $this->jsonResponse($result,null,'success');
    }

    public function drawAllLogs(Request $request)
    {
        // dd($request->period_id);
        //$result = $this->dtr_service->handleDrawRequest($request->emp_id,$request->period_id);

        $ids = DB::table('employees')->select('id')->where('emp_level','<',5)->where('exit_status',1)->get();

        foreach($ids as $emp){
            // dd($emp->biometric_id);
            $this->dtr_service->handleDrawRequest($emp->id,$request->period_id);
            $this->dtr_service->handleComputeRequest($emp->id,$request->period_id);
        }

        dd(now(),'done');
    }
}
