<?php

namespace App\Service;

use Illuminate\Support\Facades\DB;

class DTRService
{
    //
    private $day_types = ['regular','restday','special_hol','legal_hol','dbl_special','dbl_legal'];

    public function handleGetDTR($period_id,$emp_id)
    {
        $data = [];

        $collection = collect($this->day_types);

        $employee = DB::table('employees')
            ->where('id','=',$emp_id)
            ->first();

        if($employee){
            if($employee->emp_level < 6){
                $period = DB::table('payroll_period')
                    ->where('id','=',$period_id)
                    ->first();
            }else{
                $period = DB::table('payroll_period_weekly')
                    ->where('id','=',$period_id)
                    ->first();
            }

            if($period){
                foreach($collection as $type){
                    if(method_exists($this,$type)){
                        // $this->$type($employee,$period); working
                        $data[$type] = call_user_func(array($this,$type),$employee,$period);

                    }
                }
            }

            return $data;

        }
        
    }

    public function regular($employee,$period)
    {
        $result = DB::table('edtr') //edtr_detailed
            // ->where('emp_id','=',$employee->id)
            ->select(
                'time_in',
                'time_out'
            )
            ->where('biometric_id','=',$employee->biometric_id)
            ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

        return $result->get();
    }

    public function restday(){

    }

    public function special_hol(){

    }

    public function legal_hol(){

    }

    public function dbl_special(){

    }

    public function dbl_legal(){

    }

}
