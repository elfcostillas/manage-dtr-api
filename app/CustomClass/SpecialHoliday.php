<?php

namespace App\CustomClass;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SpecialHoliday extends Day
{
    //
  
    // public function save()
    // {
    //     dd('regular holiday');
    // }

    public function compute()
    {
       
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

                // $this->computeLate();
                // $this->computeUnderTime();
                $this->computeHours();

                // $this->computeNightDiff();
                // $this->computeOverTime();

            

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
                            $this->log_object->awol = 0;
                        break;
                    
                }
            }
            

            // $this->log_object->awol = 8 - $leaves;
            
        }else{
             if(!is_null($time_in)  && !is_null($time_out)){
                $this->makeSchedule(); // make schedule; set date to next day
                $this->makeworkedTime();
            
                $this->computeHours();

            }
        }

        $new_arr = CustomRequest::filter('edtr_detailed',(array) $this->log_object);

        DB::table('edtr_detailed')
            ->where('id', $this->log_object->id)
            ->update($new_arr);

        // dd(Schema::getColumnListing('edtr_detailed'));

    }

    public function computeHours()
    {
     
        $hrs = 8;
        $day = 1;

        $am_hrs = 0;

        if(!is_null($this->sched_am_time_out))
        {
            if($this->actual_time_in < $this->sched_am_time_out->sub('15 minutes')){
                $am_hrs = 4;
            }else{
                $am_hrs = 0;
            }
        }
        

        if($this->actual_time_out < $this->sched_pm_time_in){
            $pm_hrs = 0;
        }else{
            $pm_hrs = 4;
        }

        $hrs = $am_hrs + $pm_hrs;

        $leaves = $this->getFiledLeaves();

        if(get_class($this) == 'App\CustomClass\RegularDay'){
            
            switch($this->indexDay()->shortEnglishDayOfWeek)
            {
                case 'Mon' :
                case 'Tue' :
                case 'Wed' :
                case 'Thu' :
                case 'Fri' :
                    if(($hrs + $leaves) < 8)
                    {
                        $this->log_object->awol = 8 - $hrs - $leaves;
                    } 

                    break;
                case 'Sat' :
                    if(($hrs + $leaves) < 8)
                    {
                        $this->log_object->awol = 8 - $hrs - $leaves;
                    } 

                    break;

                default :
                    $this->log_object->awol = 0;
                break;
            }

        }
        
        $this->log_object->late_eq = 0;
        $this->log_object->under_time = 0;
        $this->log_object->sphol_hrs = 0;

        // $this->log_object->hrs = $hrs;
        $this->log_object->sphol_hrs = $hrs;

    }

   
}
