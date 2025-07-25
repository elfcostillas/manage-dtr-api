<?php

namespace App\Http\Controllers\Timekeeping;

use App\Http\Controllers\Controller;
use App\Repository\DTRRepository;
use App\Service\DTRService;
use Illuminate\Http\Request;

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
}
