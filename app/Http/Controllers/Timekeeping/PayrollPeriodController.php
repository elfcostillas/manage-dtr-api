<?php

namespace App\Http\Controllers\Timekeeping;

use App\Http\Controllers\Controller;
use App\Repository\SemiMonthlyPayrollPeriodRepository;
use App\Service\PayrollPeriodService;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    //
    
    public function __construct(private SemiMonthlyPayrollPeriodRepository $semi_repo,private PayrollPeriodService $service)
    {
        
    }

    public function index() {
        $result = $this->semi_repo->getSemiPayrollPeriod();
        return $this->jsonResponse($result,null,'OK');
    }

    public function list()
    {
        $result = $this->semi_repo->getPayrollPeriod();
        return $this->jsonResponse($result,null,'OK');
    }

    public function prepare(Request $request)
    {
        $result = $this->service->handleRequest($request->period_id);
        return $this->json($result,null,'OK');

    }
}
