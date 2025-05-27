<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;

class DTRRepository
{
    //

    public function getDTR($payroll_period,$employee)
    {
        // dd($payroll_period,$employee);
        $dtr = DB::table('edtr_detailed')->where('emp_id','=',$employee->id)
            ->whereBetween('dtr_date',[$payroll_period->date_from,$payroll_period->date_to]);


        return $dtr->get();
    }

    public function get_holidays($payroll_period,$employee)
    {

    }

    public function get_leaves($payroll_period,$employee)
    {
        
    }

    /**
    * @param date - dtr date
    * @param type - time in | time out | ot in | ot out
    */
    public function getLog($employee,$date,$type)
    {
        // dd($employee,$date,$type);
        switch($type){
            case 'C/In' : 
                $used =  DB::table('edtr_detailed')
                    ->select('time_in_id')
                    ->where('emp_id','=',$employee->id);
                break;
            case 'C/Out' : 
                $used =  DB::table('edtr_detailed')
                    ->select('time_out_id')
                    ->where('emp_id','=',$employee->id);
                break;
            case 'OT/In' : 
                $used =  DB::table('edtr_detailed')
                    ->select('ot_in_id')
                    ->where('emp_id','=',$employee->id);
                break;
            case 'OT/Out' : 
                $used =  DB::table('edtr_detailed')
                    ->select('ou_out_id')
                    ->where('emp_id','=',$employee->id);
                break;
        };

        $raw_dtr = DB::table('edtr_raw')
            ->where('emp_id','=',$employee->id)
            ->where('punch_date','>=',$date)
            ->where('cstate','=',$type)
            ->orderBy('punch_date','ASC')
            ->orderBy('punch_time','ASC')
            ->first();
       
        
        return $raw_dtr;
    }

}
