<?php

namespace App\Http\Controllers\TimeKeeping;

use App\Http\Controllers\Controller;
use App\Repository\SGPayrollPeriodRepository;
use App\Service\SGPayrollPeriodService;
use Illuminate\Http\Request;

class PayrollPeriodSGController extends Controller
{
    //
    public function __construct(public SGPayrollPeriodRepository $repo,public SGPayrollPeriodService $service)
    {
        
    }

    public function index()
    {
        $result = $this->repo->getPayrollPeriods();
        return $this->jsonResponse($result,null,'OK');
    }

    public function prepare(Request $request)
    {
        $result = $this->service->handleRequest($request->integer('period_id'));
        return $this->json($result,null,'OK');

    }

    public function list()
    {
        $result = $this->repo->getPayrollPeriod();
        return $this->jsonResponse($result,null,'OK');
    }

}
