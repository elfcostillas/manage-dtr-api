<?php

namespace App\Service;

use App\Repository\EmployeeRepository;
use App\Repository\SemiMonthlyPayrollPeriodRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class PayrollPeriodService
{
    //

    public function __construct(private SemiMonthlyPayrollPeriodRepository $payperiod_repo,private EmployeeRepository $emp_repo)
    {
        
    }
    public function handleRequest($period)
    {
        $period = $this->payperiod_repo->find($period);
        
        $dtr_tmp = [];

        if($period){
            $employees = $this->emp_repo->employeeActiveSemiMonthly();
            $date_from = Carbon::createFromFormat('Y-m-d',$period->date_from);
            $date_to = Carbon::createFromFormat('Y-m-d',$period->date_to);

            $dates = CarbonPeriod::create($date_from,'1 Day',$date_to);
            
            foreach($employees as $employee){
                foreach($dates as $date){
                    $dtr_array = [
                        'biometric_id' => $employee->biometric_id,
                        'emp_id' => $employee->id,
                        'dtr_date' => $date->format('Y-m-d')
                    ];
                    array_push($dtr_tmp,$dtr_array);
                }
            }

            DB::table('edtr_detailed')->insert($dtr_tmp); //insertOrIgnore 
        }

        return $period;
    }
}
