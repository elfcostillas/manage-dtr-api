<?php

namespace App\CustomClass\Logs;

use App\CustomClass\Logs\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Repository\DTRRepository;
use App\Repository\EmployeeRepository;

class ClockIn extends Log
{
    //
    public $log; 
    public $data; 

    // public function __construct($data)
    // {   
        
    //     parent::__construct();
    // }

    public function buildSelf() : void {
      
        /*
        $self = DB::table('edtr_raw_vw')
            ->select('line_id','punch_date','punch_time','biometric_id','cstate','src','src_id','emp_id','new_cstate','t_stamp')
            ->where('punch_date',$this->data->dtr_date)
            ->where('biometric_id',$this->data->biometric_id)
            ->where('cstate','=','C/In')
            ->first();
        */

        $repo = app(DTRRepository::class);
        $emp_repo = app(EmployeeRepository::class);

        $employee = $emp_repo->getEmployee($this->data->emp_id);

        $self = $repo->getLog($employee,$this->data,'C/In');


        $this->log = $self;
        
        // $this->log = $self;

        // dd($this->data->dtr_date,$this->data->biometric_id);

    }

    public function showData()
    {
        // dd($this->log,$this->data);
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getNextLogin()
    {
        
        $date = Carbon::createFromFormat('Y-m-d',$this->data->dtr_date);
        $nextDay = $date->addDay()->format('Y-m-d');

        $log = DB::table('edtr_raw_vw')
        ->select('line_id','punch_date','punch_time','biometric_id','cstate','src','src_id','emp_id','new_cstate','t_stamp')
            ->where('punch_date',$nextDay)
            ->where('biometric_id',$this->data->biometric_id)
            ->where('cstate','=','C/In')
            ->first();

        return $log;

    }

    public function getNextDaySchedule()
    {
        $date = Carbon::createFromFormat('Y-m-d',$this->data->dtr_date);
        $nextDay = $date->addDay()->format('Y-m-d');

        $nextSched = DB::table('edtr_detailed')
            ->leftJoin('work_schedules','edtr_detailed.schedule_id','=','work_schedules.id')
            ->select(DB::raw("timestamp(`edtr_detailed`.`dtr_date`,`work_schedules`.`time_in`) AS `t_stamp`"))
            ->where(function($qry){
                $qry->where('work_schedules.time_in','!=','RD');
                $qry->where('work_schedules.time_out','!=','RD');
            })
            ->where('dtr_date','=',$nextDay)
            ->where('biometric_id',$this->data->biometric_id);

        // dd($nextSched->toSql(),$nextSched->getBindings());
        
        return $nextSched->first();
    }


}
