<?php

namespace App\CustomClass\Logs;

use App\CustomClass\Logs\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClockInOT extends Log
{
    //
    public $log; 
    public $row;

    public $login;
    public $time_in;
    public $nextSchedLogin;


    public function __construct($row,$time_in,$login,$nextSchedLogin)
    {
        $this->row = $row;
        $this->time_in = $time_in;
        $this->login = $login;
        $this->nextSchedLogin = $nextSchedLogin;

        $this->buildSelf();

        // dd($row,$time_in,$login,$nextSchedLogin);
    }
    
    public function buildSelf() : void {

    
        $time = (is_null($this->row->sched_time_in) || $this->row->sched_time_in=='') ? '00:00' : $this->row->sched_time_in;
        
        $sched_in = Carbon::createFromFormat('Y-m-d H:i', $this->row->dtr_date .' '. $time)->format('Y-m-d H:i:s.u');

        $start = $this->time_in->t_stamp ?? $sched_in;
        
        if(is_null($this->nextSchedLogin))
        {
            $this->nextSchedLogin =  Carbon::createFromFormat('Y-m-d H:i', $this->row->dtr_date .' '.'12:00')->addDay();
        }

        $self = DB::table('edtr_raw_vw')
            // ->where('punch_date',$this->data->dtr_date)
            ->select('line_id','punch_date','punch_time','biometric_id','cstate','src','src_id','emp_id','new_cstate','t_stamp')
            ->where('biometric_id',$this->row->biometric_id)
            ->whereBetween('t_stamp',[$start,$this->nextSchedLogin])
            ->where('cstate','=','OT/In')
            ->first();
        
        $this->log = $self;

    }

    public function getLog()
    {
        return $this->log;
    }
    
}

/*
OT/In
OT/Out
*/