<?php

namespace App\Http\Controllers\Timekeeping;

use App\Http\Controllers\Controller;
use App\Repository\SemiMonthlyPayrollPeriod;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    //
    public function __construct(private SemiMonthlyPayrollPeriod $semi)
    {
        
    }

    public function list()
    {
        $result = $this->semi->getPayrollPeriod();
        return $this->jsonResponse($result,null,'OK');
    }
}
