<?php

namespace App\CustomClass;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Error;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Day
{
    //
    public $log_object;
    public $employee_object;
    public $period_object;

    public $sched_time_in;
    public $sched_am_time_out;

    public $sched_pm_time_in;
    public $sched_time_out;

    public $actual_time_in;
    public $actual_time_out;

    public $nightDiffStart;
    public $nightDiffEnd;

    public $ot_timein;
    public $ot_timeout;

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
        try {
            return Carbon::createFromFormat('Y-m-d H:i',$string);
        }catch(Exception | Error $e ){
            // dd($date,$time,$e);
        }
        
    }

    public function computeLate()
    {
        $late_am = 0;
        $late_pm = 0;
        $late_minutes = 0;

        //shortEnglishDayOfWeek

        if(!is_null($this->employee_object->grace_period))
        {
            // dd($this->employee_object->grace_period);
            $this->sched_time_in = $this->sched_time_in->addMinutes($this->employee_object->grace_period);
        }

        if($this->actual_time_in > $this->sched_time_in && ($this->actual_time_in < $this->sched_am_time_out->sub('15 minutes')))
        {
            $late_am = $this->actual_time_in->diff($this->sched_time_in);
            $late_minutes =  ($late_am->i + ($late_am->h * 60));
        }

        // tardy on afternoon
        if($this->actual_time_in > $this->sched_pm_time_in && ($this->actual_time_in < $this->sched_time_out))
        {
            $late_pm = $this->actual_time_in->diff($this->sched_pm_time_in);
            $late_minutes =  ($late_pm->i + ($late_pm->h * 60));
        }

        $this->log_object->late = $late_minutes;
    }

    public function computeUnderTime()
    {
        $ut_am = 0;
        $ut_pm = 0;
        $ut_mins = 0;
        $final_ut_mins = 0;
        
        if($this->actual_time_out < $this->sched_am_time_out){
            $ut_am = $this->sched_am_time_out->diff($this->actual_time_out);
            $ut_mins += ($ut_am->i + ($ut_am->h * 60));
        }

        if(($this->actual_time_out > $this->sched_pm_time_in) && ($this->actual_time_out < $this->sched_time_out) ){
            $ut_pm = $this->sched_time_out->diff($this->actual_time_out);
            // dd($this->sched_time_out,$this->actual_time_out);
            $ut_mins += ($ut_pm->i + ($ut_pm->h * 60));
        }

        if($ut_mins){
            $multiplier = ceil($ut_mins/30);
            $final_ut_mins = $multiplier * 30;
        }


        $this->log_object->under_time = $final_ut_mins;
    }

    public function computeHours()
    {
        $hrs = 8;
        $day = 1;

        $am_hrs = 0;

        // dd($this->actual_time_in < $this->sched_am_time_out->sub('15 minutes'));

        if($this->actual_time_in < $this->sched_am_time_out->sub('15 minutes')){
            $am_hrs = 4;
        }else{
            $am_hrs = 0;
        }

        if($this->actual_time_out < $this->sched_pm_time_in){
            $pm_hrs = 0;
        }else{
            $pm_hrs = 4;
        }

        $hrs = $am_hrs + $pm_hrs;

        // if($hrs != 8 && (get_class($this) == 'App\CustomClass\RegularDay')){
        if(get_class($this) == 'App\CustomClass\RegularDay'){
            
            switch($this->indexDay()->shortEnglishDayOfWeek)
            {
                case 'Mon' :
                case 'Tue' :
                case 'Wed' :
                case 'Thu' :
                case 'Fri' :
                    

                    break;
                case 'Sat' :
                    break;
            }

            $leaves = $this->getFiledLeaves();

            // $hrs += $leaves;
            // $date = Carbon::createFromFormat('Y-m-d',)
            //  dd($this->log_object)

            if(($hrs + $leaves) < 8)
            {
                $this->log_object->awol = 8 - $hrs - $leaves;
            } 

        }
        

        $this->log_object->hrs = $hrs;
        $this->log_object->ndays =  round($this->log_object->hrs/8,2);

        // if($this->log_object->dtr_date == '2025-07-21'){
        //     dd($this->log_object,$am_hrs,$pm_hrs,$leaves);
        // }
    }

    public function getFiledLeaves() : Float
    {
        $leave = DB::table('filed_leaves_vw')
            ->select(DB::raw("ifnull(SUM(with_pay + without_pay),0) as hrs"))
            ->where('biometric_id','=',$this->log_object->biometric_id)
            ->where('leave_date','=',$this->log_object->dtr_date)
            ->first();

        
            // return round($leave->hrs/8,2);
            return round($leave->hrs,2);
        
        // SELECT SUM(with_pay + without_pay) FROM filed_leaves_vw WHERE biometric_id = AND leave_date = 

        // dd($this->log_object->biometric_id,$this->log_object->dtr_date);
    }

    public function computeNightDiff()
    {
        // $range = CarbonPeriod::make();

        $nightDiff = 0;
        $nightDiffOT = 0;

        $range = CarbonPeriod::create($this->nightDiffStart,'1 Minute',$this->nightDiffEnd);
       
        foreach($range as $indexMinunte){
            //echo $indexMinunte . '-';
            if($indexMinunte->between($this->actual_time_in,$this->actual_time_out)){
                $nightDiff++;
            }

            if($indexMinunte->between($this->ot_timein,$this->ot_timeout)){
                $nightDiffOT++;
            }
            
        }

        if($nightDiff > 0){
            $nightDiff =  $nightDiff - ($nightDiff % 30);
            $this->log_object->night_diff = round($nightDiff/60,2);
        }

        if($nightDiffOT > 0){
            $nightDiffOT =  $nightDiffOT - ($nightDiffOT % 30);
            $this->log_object->night_diff_ot = round($nightDiffOT/60,2);
        }

    }

    public function computeOverTime()
    {
        $overtime = 0;

        if(!is_null($this->ot_timein) && !is_null($this->ot_timeout)){

            $range = CarbonPeriod::create($this->ot_timein,'1 Minute',$this->ot_timeout);
            foreach($range as $indexMinunte){
                $overtime++;
            }

            $overtime = $overtime - ($overtime % 30);
        }
      
        $this->log_object->over_time = round($overtime/60,2);
    }

    public function compute()
    {
        // dd($this->log_object->hol_code);
       
        $time_in = $this->log_object->time_in;
        $time_out = $this->log_object->time_out;

        $mins = 0;
        $hrs = 0;
        $late_minutes = 0;
        $ndays = 0;
        $awol = 0;


        $this->log_object->hrs = 0;
        $this->log_object->ndays = 0;
        $this->log_object->awol = 0;
        $this->log_object->late_minutes = 0;

        if(is_null($this->log_object->hol_code)){

            if(!is_null($time_in)  && !is_null($time_out)){
                $this->makeSchedule(); // make schedule; set date to next day
                // dd($this->convertToTime($this->log_object->dtr_date,$this->log_object->time_in)->format('Y-m-d H:i'));
                $this->makeworkedTime();
            
                // $period = CarbonPeriod::create($this->actual_time_in,'1 Minute',$this->actual_time_out);

                $this->computeLate();
                $this->computeUnderTime();
                $this->computeHours();

                $this->computeNightDiff();
                $this->computeOverTime();

            

            }else{

                /* for ni clock-in / clock-out */

                $date = Carbon::createFromFormat('Y-m-d',$this->log_object->dtr_date);

                switch($date->shortDayName)
                {
                    case 'Mon' :
                    case 'Tue' :
                    case 'Wed' :
                    case 'Thu' :
                    case 'Fri' :
                        $leaves = $this->getFiledLeaves();
                            $this->log_object->awol = 8 - $leaves;
                        break;

                    case 'Sat' :
                        // dd($this->log_object );
                        $leaves = $this->getFiledLeaves();
                        if(!is_null($this->log_object->schedule_id) && $this->log_object->schedule_id != 0 ){
                            $this->log_object->awol = 8 - $leaves;
                        } else {
                            $this->log_object->awol = 0;
                        }

                        break;


                    case 'Sun' :

                        break;
                    
                }
            }
            

            // $this->log_object->awol = 8 - $leaves;
            
        }

        $new_arr = CustomRequest::filter('edtr_detailed',(array) $this->log_object);

        DB::table('edtr_detailed')
            ->where('id', $this->log_object->id)
            ->update($new_arr);

        // dd(Schema::getColumnListing('edtr_detailed'));

    }

    public function makeSchedule()
    {
        $this->sched_time_in = $this->convertToTime($this->log_object->dtr_date,$this->log_object->sched_time_in);

        if(strtotime($this->log_object->sched_time_in) < strtotime($this->log_object->sched_out_am)){
            $this->sched_am_time_out = $this->convertToTime($this->log_object->dtr_date,$this->log_object->sched_out_am);
        }else{
            $this->sched_am_time_out = $this->convertToTime($this->convertToDate($this->log_object->dtr_date)->addDay()->format('Y-m-d'),$this->log_object->sched_out_am);
        }

        if(strtotime($this->log_object->sched_time_in) < strtotime($this->log_object->sched_in_pm)){
            $this->sched_pm_time_in = $this->convertToTime($this->log_object->dtr_date,$this->log_object->sched_in_pm);
        }else{
            $this->sched_pm_time_in = $this->convertToTime($this->convertToDate($this->log_object->dtr_date)->addDay()->format('Y-m-d'),$this->log_object->sched_in_pm);
        }

        if(strtotime($this->log_object->sched_time_in) < strtotime($this->log_object->sched_time_out)){
            $this->sched_time_out = $this->convertToTime($this->log_object->dtr_date,$this->log_object->sched_time_out);
        }else{
            $this->sched_time_out = $this->convertToTime($this->convertToDate($this->log_object->dtr_date)->addDay()->format('Y-m-d'),$this->log_object->sched_time_out);
        }

        //make night diff time
        $this->nightDiffStart =  $this->convertToTime($this->log_object->dtr_date,'22:00');
        $this->nightDiffEnd =  $this->convertToTime($this->convertToDate($this->log_object->dtr_date)->addDay()->format('Y-m-d'),'06:00');

        if($this->log_object->ot_in != null && $this->log_object->ot_out != null){
            // dd($this->log_object->ot_in); check if overtime timein < timein ? same day : next day

            if(Carbon::parse($this->log_object->time_in) < Carbon::parse($this->log_object->ot_in)){
                $this->ot_timein = $this->convertToTime($this->log_object->dtr_date,$this->log_object->ot_in);
            } else {
                $this->ot_timein = $this->convertToTime($this->convertToDate($this->log_object->dtr_date)->addDay()->format('Y-m-d'),$this->log_object->ot_in);
            }

            if(Carbon::parse($this->log_object->time_in) < Carbon::parse($this->log_object->ot_out)){
                $this->ot_timeout = $this->convertToTime($this->log_object->dtr_date,$this->log_object->ot_out);
            } else {
                $this->ot_timeout = $this->convertToTime($this->convertToDate($this->log_object->dtr_date)->addDay()->format('Y-m-d'),$this->log_object->ot_out);
            }

           
            
        }
        
        //ot_timeout

        // dd( $this->sched_time_in,$this->sched_am_time_out, $this->sched_pm_time_in,$this->sched_time_out);

            //$this->sched_am_time_out;

        // $this->sched_pm_time_in;

        /* 
            check if schedules drags to next day  
            if true set the date to next date 
        */
      
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

foreach($period as $minute){
              /*
                dd($minute,$this->sched_time_in,$this->sched_am_time_out);
                if($minute < $this->actual_time_in && ($minute->between($this->sched_time_in,$this->sched_am_time_out))){
                    $late_minutes++;
                    dd('imlate');
                }

                if($minute < $this->actual_time_in && ($minute->between($this->sched_pm_time_in,$this->sched_time_out))){
                    $late_minutes++;
                     dd('imlate');
                }

               
                if($minute->between($this->sched_time_in,$this->sched_am_time_out) || $minute->between($this->sched_pm_time_in,$this->sched_time_out))
                {
                    $mins++;
                }else{
                    if($this->actual_time_in->between($this->sched_time_in->addMinute(),$this->sched_am_time_out)){
                        $late_minutes++;
                        // echo $minute->format('Y-m-d H:i').'<br>';
                        dd(
                            $this->sched_time_in,
                            $this->actual_time_in,
                            $minute

                        );
                    }

                    if($this->actual_time_in->between($this->sched_pm_time_in->addMinute(),$this->sched_time_out)){
                        $late_minutes++;
                        // echo $minute->format('Y-m-d H:i').'<br>';
                    }
                }
                   
            }

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