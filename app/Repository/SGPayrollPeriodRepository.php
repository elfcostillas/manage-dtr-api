<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;

class SGPayrollPeriodRepository
{
    //

    public function  find($period_id)
    {
        return DB::table('payroll_period_weekly')->where('id',$period_id)->first();
    }


    public function getPayrollPeriods()
    {
        $result = DB::table('payroll_period_weekly')
        ->select('id','date_from','date_to','date_release','man_hours','pyear','cut_off')
        ->orderBy('id','desc');
        
        return $result->get();
    }

    
    public function getPayrollPeriod()
    {
        $result = DB::table('payroll_period_weekly')
        ->select(DB::raw("id,concat(DATE_FORMAT(date_from,'%m/%d/%Y'),'-',DATE_FORMAT(date_to,'%m/%d/%Y')) as label"))
        ->orderBy('id','desc');
        
        return $result->get();
    }
}
