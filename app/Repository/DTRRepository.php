<?php

namespace App\Repository;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DTRRepository
{
    //

    public function sched() {
        $result = DB::table('work_schedules')
                ->select(DB::raw("id,CONCAT(time_in,'-',time_out) label"))
                ->orderBy('time_in','asc');

        return $result->get();
    }

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
    public function getLog($employee,$row,$type)
    {
        // dd($employee,$row->dtr_date,$type);
        $carbondate = Carbon::createFromFormat('Y-m-d',$row->dtr_date);
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
                // dd($row);
                $used =  DB::table('edtr_detailed')
                    ->select('ot_out_id')
                    ->whereNotNull('ot_out_id')
                    ->where('emp_id','=',$employee->id);

                $time_in_of_the_day =  DB::table('edtr_raw')
                    ->where('emp_id','=',$employee->id)
                    ->where('punch_date','>=',$row->dtr_date)
                    ->where('cstate','=','C/In')
                    ->whereNotIn('line_id',$used)
                    ->orderBy('punch_date','ASC')
                    ->orderBy('punch_time','ASC')
                    ->first();
                break;

               
        };

        $raw_dtr = DB::table('edtr_raw')
            ->where('emp_id','=',$employee->id);
            if($type == 'C/In'){
                $raw_dtr->where('punch_date','=',$row->dtr_date);
            }else{
                $raw_dtr->whereBetween('punch_date',[$carbondate->format('Y-m-d'),$carbondate->addDay()->format('Y-m-d')]);
            }
            // ->where('punch_date','>=',$row->dtr_date)
        
            $raw_dtr->where('cstate','=',$type)
            ->whereNotIn('line_id',$used);

            // check here if timeout < timein sched
            // dd();
            if(isset($time_in_of_the_day) && ($row->sched_time_in > $row->sched_time_out)){
                // dd($time_in_of_the_day,$row->dtr_date);
                $raw_dtr->where('punch_time','<',$time_in_of_the_day->punch_time);
            }

            $raw_dtr->orderBy('punch_date','ASC')
                ->orderBy('punch_time','ASC');
            
            // if($type == 'OT/Out'){
            //     dd($row, $raw_dtr->toSql(),$raw_dtr->getbindings());
            // }
        
        return $raw_dtr->first();
    }

    public function getLogsToFillOut($payroll_period,$employee)
    {
        $holidays = $this->get_holidays($payroll_period,$employee);

        $dtr = DB::table('edtr_detailed')
                ->leftJoinSub($holidays,'holidays',function($join){
                    $join->on('holidays.holiday_date','=','edtr_detailed.dtr_date');
                })
                ->leftJoin('work_schedules','edtr_detailed.schedule_id','=','work_schedules.id')
                ->where('emp_id','=',$employee->id)
                ->whereBetween('dtr_date',[$payroll_period->date_from,$payroll_period->date_to])
                ->whereNull('edtr_detailed.time_out')
                ->whereNull('edtr_detailed.ot_in')
                ->whereNotNull('edtr_detailed.time_in')
                ->whereNotNull('edtr_detailed.ot_out')
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

    // public function fillOutLogOut($dtr)
    // {
    //     foreach($dtr as $log){
    //         dd($log);
    //     }    
    // }

    public function makeRawLog($row)
    {
        $array_time_out = [
            'punch_date' => $row->dtr_date,
            'punch_time' => $row->sched_time_out,
            'biometric_id' => $row->biometric_id,
            'cstate' => 'C/Out',
            'src' => 'fill-out',
            'src_id' => null,
            'emp_id' => $row->emp_id,
            'new_cstate' => null
        ];

        $array_ot_in = [
            'punch_date' => $row->dtr_date,
            'punch_time' => $row->sched_time_out,
            'biometric_id' => $row->biometric_id,
            'cstate' => 'OT/In',
            'src' => 'fill-out',
            'src_id' => null,
            'emp_id' => $row->emp_id,
            'new_cstate' => null
        ];

        $id1 = DB::table('edtr_raw')->insertGetId($array_time_out);
        $id2 = DB::table('edtr_raw')->insertGetId($array_ot_in);

        return [
            'time_out' => $id1,
            'ot_in' => $id2,
        ];
    }

    public function clearMadeLogs($payroll_period,$employee)
    {
        $rawLogs = DB::table('edtr_raw')
            ->whereBetween('punch_date',[$payroll_period->date_from,$payroll_period->date_to])
            ->where('src','fill-out')
            ->where('emp_id',$employee->id)
            ->get();

        foreach($rawLogs as $log)
        {
            switch($log->cstate){
                case 'C/Out':
                    $log = DB::table('edtr_detailed')
                            ->where('time_out_id','=',$log->line_id)
                            ->where('dtr_date','=',$log->punch_date)
                           
                            ->first();
                    if($log){
                        $log->time_out = null;
                        $log->time_out_id = null;

                        DB::table('edtr_detailed')->where('id','=',$log->id)->update((array) $log);
                    }

                    break;

                case 'OT/In':
                    $log = DB::table('edtr_detailed')
                            ->where('ot_in_id','=',$log->line_id)
                            ->where('dtr_date','=',$log->punch_date)
                            
                            ->first();
                    if($log){
                        $log->ot_in = null;
                        $log->ot_in_id = null;

                        DB::table('edtr_detailed')->where('id','=',$log->id)->update((array) $log);
                    }

                    break;

            }

            DB::table('edtr_raw')
            ->whereBetween('punch_date',[$payroll_period->date_from,$payroll_period->date_to])
            ->where('src','fill-out')
            ->where('emp_id',$employee->id)
            ->delete();
        }

        // dd($rawLogs);
    }

}
// C/Out // OT/In