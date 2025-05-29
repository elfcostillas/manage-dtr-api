<?php

namespace App\CustomClass;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Day
{
    //
    public $log_object;
    public $employee_object;
    public $period_object;

    public $sched_time_in;
    public $sched_time_out;

    public $actual_time_in;
    public $actual_time_out;

    public $data_arr = [

    ];

    public function __construct($log_object,$employee_object,$period_object)
    {
        $this->log_object = $log_object;
        $this->employee_object = $employee_object;
        $this->period_object = $period_object;
    }

    public function isRestDay()
    {
        
        // dd($this->indexDay()->shortEnglishDayOfWeek); 

    }

    public function indexDay()
    {
        return Carbon::createFromFormat('Y-m-d',$this->log_object->dtr_date);
    }

    public function convertToDate($date)
    {
        return Carbon::createFromFormat('Y-m-d',$date);
    }

    public function convertToTime($date,$time)
    {
        $string = $date.' '.$time;
        return Carbon::createFromFormat('Y-m-d H:i',$string);
    }

    public function compute()
    {
       
        $time_in = $this->log_object->time_in;
        $time_out = $this->log_object->time_out;

        $this->makeSchedule(); // make schedule; set date to next day
       

        if(!is_null($time_in) && !is_null($time_out)){
            // dd($this->convertToTime($this->log_object->dtr_date,$this->log_object->time_in)->format('Y-m-d H:i'));
            $this->makeworkedTime();

            $period = CarbonPeriod::create($this->actual_time_in,'1 Minute',$this->actual_time_out);

            foreach($period as $minute){
                

                // echo $minute->format('Y-m-d H:i') .'<br>';
                if($minute->between($this->sched_time_in,$this->sched_time_out))
                {
                    dd('count');
                }
            }
        }else{

        }

    }

    public function makeSchedule()
    {
        $this->sched_time_in = $this->convertToTime($this->log_object->dtr_date,$this->log_object->sched_time_in);

        /* 
            check if schedules drags to next day  
            if true set the date to next date 
        */
        if(strtotime($this->log_object->sched_time_in) < strtotime($this->log_object->sched_time_out)){
            $this->sched_time_out = $this->convertToTime($this->log_object->dtr_date,$this->log_object->sched_time_out);
        }else{
            $this->sched_time_out = $this->convertToTime($this->convertToDate($this->log_object->dtr_date)->addDay()->format('Y-m-d'),$this->log_object->sched_time_out);
        }
    }

    public function makeworkedTime()
    {
        $this->actual_time_in = $this->convertToTime($this->log_object->dtr_date,$this->log_object->time_in);
        
        if(strtotime($this->log_object->time_in) < strtotime($this->log_object->time_out)){
            $this->actual_time_out = $this->convertToTime($this->log_object->dtr_date,$this->log_object->time_out);
        }else{
            $this->actual_time_out = $this->convertToTime($this->convertToDate($this->log_object->dtr_date)->addDay()->format('Y-m-d'),$this->log_object->time_out);
        }
    }
}

/*
  // dd($time_in,Carbon::parse($time_in));
            // dd($this->log_object);
            // dd(strtotime('01:00'),Carbon::parse(strtotime('01:00'))->format('Y-m-d H:i'));
            // dd(str_to_time(''));

  +"id": 1
  +"biometric_id": 158
  +"dtr_date": "2025-04-01"
  +"time_in": null
  +"time_in_id": null
  +"time_out": null
  +"time_out_id": null
  +"late": 0
  +"late_eq": "0.00"
  +"under_time": "0.00"
  +"over_time": "0.00"
  +"night_diff": "0.00"
  +"night_diff_ot": "0.00"
  +"schedule_id": 1
  +"ndays": "0.00"
  +"vl_wp": "0.00"
  +"vl_wop": "0.00"
  +"sl_wp": "0.00"
  +"sl_wop": "0.00"
  +"bl": "0.00"
  +"ot_in": null
  +"ot_in_id": null
  +"ot_out": null
  +"ot_out_id": null
  +"restday_hrs": "0.00"
  +"restday_ot": "0.00"
  +"restday_nd": "0.00"
  +"restday_ndot": "0.00"
  +"reghol_pay": "0.00"
  +"reghol_hrs": "0.00"
  +"reghol_ot": "0.00"
  +"reghol_rd": "0.00"
  +"reghol_rdnd": "0.00"
  +"reghol_rdot": "0.00"
  +"reghol_nd": "0.00"
  +"reghol_ndot": "0.00"
  +"sphol_pay": "0.00"
  +"sphol_hrs": "0.00"
  +"sphol_ot": "0.00"
  +"sphol_rd": "0.00"
  +"sphol_rdnd": "0.00"
  +"sphol_rdot": "0.00"
  +"sphol_nd": "0.00"
  +"sphol_ndot": "0.00"
  +"dblhol_pay": "0.00"
  +"dblhol_hrs": "0.00"
  +"dblhol_ot": "0.00"
  +"dblhol_rd": "0.00"
  +"dblhol_rdnd": "0.00"
  +"dblhol_rdot": "0.00"
  +"dblhol_nd": "0.00"
  +"dblhol_ndot": "0.00"
  +"reghol_rdndot": "0.00"
  +"sphol_rdndot": "0.00"
  +"dblhol_rdndot": "0.00"
  +"loc_id": null
  +"posted": "N"
  +"cont": "N"
  +"awol": "0.00"
  +"other_leave": null
  +"mlpl": "0.00"
  +"emp_id": 2
  +"hol_code": "reghol"
  +"sched_time_in": "07:45"
  +"sched_time_out": "17:00"
  +"sched_out_am": "12:00"
  +"sched_in_pm": "13:00"
  */