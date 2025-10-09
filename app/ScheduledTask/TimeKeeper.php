<?php

namespace App\ScheduledTask;

use App\Service\DTRService;
use Illuminate\Support\Facades\DB;

class TimeKeeper
{
    //
    protected $yesterday;

    protected $payroll_period;

    protected $employees;

    protected $service;
    

    public function __construct()
    {
        echo 'START :: '. now() .'<br>';

        $this->service =  app(DTRService::class);
        $this->yesterday = now()->subDay();

        $this->employees = DB::table('employees')->where('exit_status',1)->get();

        foreach($this->employees as $employee)
        {
            echo $employee->lastname.', '.$employee->firstname." - [{$employee->biometric_id}] // ".now()." <br>";
            $period = $this->payroll_period_factory($employee);

            if(!is_null($period) && !is_null($employee))
            {
                $this->service->handleDrawRequest($employee->id,$period);
                $this->service->handleComputeRequest($employee->id,$period);
            }

            


        }

        echo 'END :: '. now() .'<br>';



        // $this->payroll_period_sg = DB::table('payroll_period_weekly')
        // ->whereBetweenColumns($this->yesterday->format('Y-m-d'),['date_from','date_to'])
        // ->first();

        
        // $this->payroll_period_jlr = DB::table('payroll_period')
        // ->whereBetweenColumns($this->yesterday->format('Y-m-d'),['date_from','date_to'])
        // ->first();

        // dd($this->payroll_period_sg,$this->payroll_period_jlr);

        
    }

    public function payroll_period_factory($employee)
    {
        
        if($employee->emp_level > 5)
        {
            $period  = DB::table('payroll_period_weekly')->whereRaw(DB::raw( "'{$this->yesterday->format('Y-m-d')}' BETWEEN date_from and date_to"))->first();
        }else{
            // $period  = DB::table('payroll_period');
            $period  = DB::table('payroll_period')->whereRaw(DB::raw( "'{$this->yesterday->format('Y-m-d')}' BETWEEN date_from and date_to"))->first();
        }

        return $period;
    }
}
