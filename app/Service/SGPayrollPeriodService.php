<?php

namespace App\Service;

use App\Repository\EmployeeRepository;
use App\Repository\SGPayrollPeriodRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class SGPayrollPeriodService
{
    //
    public function __construct(private SGPayrollPeriodRepository $payperiod_repo, private EmployeeRepository $emp_repo)
    {
        
    }

    public function handleRequest($period_id)
    {
        $period = $this->payperiod_repo->find($period_id);
        
        $dtr_tmp = [];

        if($period){
            $employees = $this->emp_repo->employeeActiveSG();
            $date_from = Carbon::createFromFormat('Y-m-d',$period->date_from);
            $date_to = Carbon::createFromFormat('Y-m-d',$period->date_to);

            $dates = CarbonPeriod::create($date_from,'1 Day',$date_to);

            foreach($employees as $employee){
              
                foreach($dates as $date){

                    // dd($date->shortEnglishDayOfWeek);
                    $sched = null;
                    switch($date->shortEnglishDayOfWeek){
                        case 'Sat' :
                                $sched = $employee->sched_sat;
                            break;

                        case 'Sun' :
                                $sched = null;  //$employee->sched_mtwtf;
                            break;
                        default :
                                $sched = $employee->sched_mtwtf;
                            break;
                    };

                    $dtr_array = [
                        'biometric_id' => $employee->biometric_id,
                        'emp_id' => $employee->id,
                        'dtr_date' => $date->format('Y-m-d'),
                        'schedule_id' => $sched
                    ];
                    // array_push($dtr_tmp,$dtr_array);

                    DB::table('edtr_detailed')
                    ->updateOrInsert(
                        ['biometric_id' =>$employee->biometric_id , 'emp_id' => $employee->id, 'dtr_date' => $date->format('Y-m-d') ],
                        [ 'schedule_id' => $sched ]
                    );
                }
            }
        }


        return $period;
    }
}
