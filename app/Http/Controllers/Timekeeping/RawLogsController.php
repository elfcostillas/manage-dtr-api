<?php

namespace App\Http\Controllers\Timekeeping;

use App\Http\Controllers\Controller;
use App\Repository\DTRRepository;
use App\Service\DTRService;
use Illuminate\Http\Request;

class RawLogsController extends Controller
{
    //
    public function __construct(public DTRService $dtr_service, public DTRRepository $dtr_repo)
    {
        
    }

    public function data(Request $request)
    {   

        $result = $this->dtr_service->handleGetRawLogs((int) $request->period_id,(int) $request->emp_id);
        
        return $this->jsonResponse($result,null,'success');
    }

    public function updateLog(Request $request) {
        $result = $this->dtr_service->handleUpdatelogRequest($request->all());
        return $this->json($result,null,'success');
    }
}
