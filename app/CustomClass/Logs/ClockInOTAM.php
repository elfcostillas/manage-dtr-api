<?php

namespace App\CustomClass\Logs;

use App\CustomClass\Logs\Log;
use Illuminate\Support\Facades\DB;

class ClockInOTAM extends Log
{
    //

    public $log;
    public $row;

    public function __construct($row)
    {
        $this->row = $row;
        $this->buildSelf();
    }

    public function buildSelf() : void {
      
        $self = DB::table('edtr_raw_vw')
            ->select('line_id','punch_date','punch_time','biometric_id','cstate','src','src_id','emp_id','new_cstate','t_stamp')
            ->where('biometric_id',$this->row->biometric_id)
            ->where('punch_date',$this->row->dtr_date)
            ->where('cstate','=','OT/InAM')
            ->first();
        
        $this->log = $self;
    }

    public function getLog()
    {
        return $this->log;
    }
}

