<?php

namespace App\Service;

use App\Repository\DTRRepository;
use App\Repository\EmployeeRepository;
use App\Repository\SemiMonthlyPayrollPeriodRepository;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DTRService
{
    //
    public function __construct(private DTRRepository $dtr_repo,private EmployeeRepository $emp_repo,private SemiMonthlyPayrollPeriodRepository $payperiod_repo)
    {
        
    }
    private $day_types = ['regular','restday','special_hol','legal_hol','dbl_special','dbl_legal'];

    public function handleGetDTR($period_id,$emp_id)
    {
        $data = [];
        $data['raw_logs'] = [];

        $collection = collect($this->day_types);

        $employee = DB::table('employees')
            ->where('id','=',$emp_id)
            ->first();

        if($employee){
            if($employee->emp_level < 6){
                $period = DB::table('payroll_period')
                    ->where('id','=',$period_id)
                    ->first();
            }else{
                $period = DB::table('payroll_period_weekly')
                    ->where('id','=',$period_id)
                    ->first();
            }

            if($period){
                foreach($collection as $type){
                    if(method_exists($this,$type)){
                        // $this->$type($employee,$period); working
                        $data[$type] = call_user_func(array($this,$type),$employee,$period);

                    }
                }
            }

            $data['raw_logs'] = $this->raw_logs($employee,$period);

            return $data;

        }
        
    }

    public function handeDrawRequest($emp_id,$period_id)
    {   
        $employee = $this->emp_repo->getEmployee($emp_id);
        $period = $this->payperiod_repo->find($period_id);

        $dtr = $this->dtr_repo->getDTR($period,$employee);

        foreach($dtr as $row){
            // time in
            $time_in = $this->dtr_repo->getLog($employee,$row->dtr_date,'C/In');
            
            //time out
            $time_out = $this->dtr_repo->getLog($employee,$row->dtr_date,'C/Out');
            
            //ot in 
            $ot_in = $this->dtr_repo->getLog($employee,$row->dtr_date,'OT/In');

            //ot out
            $ot_out = $this->dtr_repo->getLog($employee,$row->dtr_date,'OT/Out');

            if($time_in){
                $row->time_in_id = $time_in->line_id;
                $row->time_in = $time_in->punch_time;
            }

            if($time_out){
                $row->time_out_id = $time_out->line_id;
                $row->time_out = $time_out->punch_time;
            }

            if($ot_in){
                 $row->ot_in_id = $ot_in->line_id;
                $row->ot_in = $ot_in->punch_time;
            }

            if($ot_out){
                $row->ou_out_id = $ot_out->line_id;
                $row->ou_out = $ot_out->punch_time;
            }

            DB::table('edtr_detailed')
            ->where('id', $row->id)
            ->update((array) $row);

        }
        
        return $dtr;
        
    }

    public function raw_logs($employee,$period)
    {
        // dd($period);
        $date_from = Carbon::createFromFormat('Y-m-d',$period->date_from);
        $date_to = Carbon::createFromFormat('Y-m-d',$period->date_to)->add('1 Day');
        //select line_id,punch_date,punch_time,cstate,src,src_id,emp_id,biometric_id 
        //from edtr_raw where biometric_id =  and punch_date 

        $result = DB::table('edtr_raw') //edtr_detailed
            ->where('biometric_id','=',$employee->biometric_id)
            ->select('line_id','punch_date','punch_time','cstate','src','src_id','emp_id','biometric_id')
            ->whereBetween('punch_date',[$date_from->format('Y-m-d'),$date_to->format('Y-m-d')]);

        return $result->orderBy('punch_date','ASC')->orderBy('punch_time','ASC')->get();
    }

    // ->where('biometric_id','=',$employee->biometric_id)

    public function regular($employee,$period)
    {
        $result = DB::table('edtr_detailed') //edtr_detailed
            ->where('emp_id','=',$employee->id)
            ->select(
                'id',
                'emp_id',
                'dtr_date',
                'time_in',
                'time_out',
                'time_in_id',
                'time_out_id'
            )
            ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

        return $result->orderBy('dtr_date','ASC')->get();
    }

    public function restday($employee,$period)
    {
            $result = DB::table('edtr_detailed') //edtr_detailed
            ->where('emp_id','=',$employee->id)
            ->select(
                'id',
                'emp_id',
                'dtr_date',
                'time_in',
                'time_out',
                'time_in_id',
                'time_out_id'
            )
            ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

         return $result->orderBy('dtr_date','ASC')->get();
    }

    public function special_hol($employee,$period)
    {
        $result = DB::table('edtr_detailed') //edtr_detailed
            ->where('emp_id','=',$employee->id)
            ->select(
                'id',
                'emp_id',
                'dtr_date',
                'time_in',
                'time_out',
                'time_in_id',
                'time_out_id'
            )
            ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

        return $result->orderBy('dtr_date','ASC')->get();
    }

    public function legal_hol($employee,$period)
    {
        $result = DB::table('edtr_detailed') //edtr_detailed
            ->where('emp_id','=',$employee->id)
            ->select(
                'id',
                'emp_id',
                'dtr_date',
                'time_in',
                'time_out',
                'time_in_id',
                'time_out_id'
            )
            ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

        return $result->orderBy('dtr_date','ASC')->get();
    }

    public function dbl_special($employee,$period)
    {
        $result = DB::table('edtr_detailed') //edtr_detailed
            ->where('emp_id','=',$employee->id)
            ->select(
                'id',
                'emp_id',
                'dtr_date',
                'time_in',
                'time_out',
                'time_in_id',
                'time_out_id'
            )
            ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

         return $result->orderBy('dtr_date','ASC')->get();
    }

    public function dbl_legal($employee,$period)
    {
        $result = DB::table('edtr_detailed') //edtr_detailed
            ->where('emp_id','=',$employee->id)
            ->select(
                'id',
                'emp_id',
                'dtr_date',
                'time_in',
                'time_out',
                'time_in_id',
                'time_out_id'
            )
            ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

        return $result->orderBy('dtr_date','ASC')->get();
    }

}
