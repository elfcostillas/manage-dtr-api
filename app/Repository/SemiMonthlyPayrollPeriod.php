<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;

class SemiMonthlyPayrollPeriod
{
    //
    public function getPayrollPeriod()
    {
        $result = DB::table('payroll_period')
        ->select(DB::raw("id,concat(DATE_FORMAT(date_from,'%m/%d/%Y'),'-',DATE_FORMAT(date_to,'%m/%d/%Y')) as label"))
        ->orderBy('id','desc');
        
        return $result->get();
    }
}
