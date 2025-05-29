<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;

class DTRRepository
{
    //

    public function getDTR($payroll_period,$employee)
    {
        // dd($payroll_period,$employee);
        $holidays = $this->get_holidays($payroll_period,$employee);

        $dtr = DB::table('edtr_detailed')
                ->leftJoinSub($holidays,'holidays',function($join){
                    $join->on('holidays.holiday_date','=','edtr_detailed.dtr_date');
                })
                ->leftJoin('work_schedules','edtr_detailed.schedule_id','=','work_schedules.id')
                ->where('emp_id','=',$employee->id)
                ->whereBetween('dtr_date',[$payroll_period->date_from,$payroll_period->date_to])
                ->select(DB::raw("
                edtr_detailed.*,
                hol_code,
                work_schedules.time_in as sched_time_in,
                work_schedules.time_out as sched_time_out,
                work_schedules.out_am as sched_out_am,
                work_schedules.in_pm as sched_in_pm
                "))
                ->orderBy('dtr_date','ASC');

        return $dtr->get();
    }

    public function get_holidays($payroll_period,$employee)
    {
        /*
        SELECT * FROM holidays INNER JOIN holiday_types ON holidays.holiday_type = holiday_types.id
        INNER JOIN holiday_location ON holidays.id = holiday_location.holiday_id
        */

        $result = DB::table('holidays')
            ->join('holiday_types','holidays.holiday_type','=','holiday_types.id')
            ->join('holiday_location','holidays.id','=','holiday_location.holiday_id')
            ->select(DB::raw("holidays.*,holiday_types.hol_code"))
            ->where('location_id','=',$employee->location_id)
            ->whereBetween('holiday_date',[$payroll_period->date_from,$payroll_period->date_to]);
        
        return $result;
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
                    ->whereNotNull('time_in_id')
                    ->where('emp_id','=',$employee->id);
                break;
            case 'C/Out' : 
                $used =  DB::table('edtr_detailed')
                    ->select('time_out_id')
                    ->whereNotNull('time_out_id')
                    ->where('emp_id','=',$employee->id);
                break;
            case 'OT/In' : 
                $used =  DB::table('edtr_detailed')
                    ->select('ot_in_id')
                    ->whereNotNull('ot_in_id')
                    ->where('emp_id','=',$employee->id);
                break;
            case 'OT/Out' : 
                $used =  DB::table('edtr_detailed')
                    ->select('ot_out_id')
                    ->whereNotNull('ot_out_id')
                    ->where('emp_id','=',$employee->id);
                break;
        };

       

        $raw_dtr = DB::table('edtr_raw')
            ->where('emp_id','=',$employee->id)
            ->where('punch_date','=',$date)
            ->where('cstate','=',$type)
            ->whereNotIn('line_id',$used)
            ->orderBy('punch_date','ASC')
            ->orderBy('punch_time','ASC')
            ->first();
       
        
        return $raw_dtr;
    }

}
