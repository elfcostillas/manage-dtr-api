<?php

namespace App\Service;

use App\CustomClass\CustomRequest;
use App\CustomClass\LegalHoliday;
use App\CustomClass\Logs\ClockIn;
use App\CustomClass\Logs\ClockInOT;
use App\CustomClass\Logs\ClockInOTAM;
use App\CustomClass\Logs\ClockOut;
use App\CustomClass\Logs\ClockOutOT;
use App\CustomClass\Logs\ClockOutOTAM;
use App\CustomClass\RegularDay;
use App\CustomClass\SpecialHoliday;
use App\Repository\DTRRepository;
use App\Repository\EmployeeRepository;
use App\Repository\SemiMonthlyPayrollPeriodRepository;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use function Psy\debug;

class DTRService
{
    //
    public function __construct(private DTRRepository $dtr_repo,private EmployeeRepository $emp_repo,private SemiMonthlyPayrollPeriodRepository $payperiod_repo)
    {
        
    }

    public $employee;
    
    //private $day_types = ['regular','restday','special_hol','legal_hol','dbl_special','dbl_legal'];
    private $day_types = ['regular','special_hol','legal_hol','dbl_special','dbl_legal'];

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
            $data['sched'] = $this->dtr_repo->sched();

            return $data;

        }
        
    }

    public function handleGetRawLogs($period_id,$emp_id)
    {
        $data = [];

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
               $data['raw_logs'] = $this->raw_logs($employee,$period);
            }
            return $data;
        }
    }

    public function handleDrawRequest($emp_id,$period_id)
    {   
        // DB::enableQueryLog();

        $employee = $this->emp_repo->getEmployee($emp_id);
        
        if(!is_null($employee)){
            $this->employee = $employee;
        }

        $period = $this->payperiod_repo->find($period_id);
        
        /* prepare DTR by setting biometric_id */
        // echo now().'clear raw logs  <br>';
        $this->prepareDtrRaw($emp_id);
        // echo now().'get dtr  <br>';
        $dtr = $this->dtr_repo->getDTR($period,$employee);
        // echo now().'clear dtr  <br>';
        $this->clearDailyLogsToProcess($dtr);
       

        foreach($dtr as $row){

            $ot_in_am_obj = new ClockInOTAM($row);
            $ot_in_am = $ot_in_am_obj->getLog();

            if(!is_null($ot_in_am)){
                $row->ot_in_am_id = $ot_in_am->line_id;
                $row->ot_in_am = $ot_in_am->punch_time;
            }

            $ot_out_out_obj = new ClockOutOTAM($row);
            $ot_out_out = $ot_out_out_obj->getLog();

            if(!is_null($ot_out_out)){
                $row->ot_out_am_id = $ot_out_out->line_id;
                $row->ot_out_am = $ot_out_out->punch_time;
            }
            
          
            $clock_in_obj = new ClockIn($row);

            $time_in = $clock_in_obj->getLog();

            if(!is_null($time_in)){

                $row->time_in_id = $time_in->line_id;
                $row->time_in = $time_in->punch_time;
            }
           
            $nextLogin = $clock_in_obj->getNextLogin();
           
            $nextDaySched = (is_null($clock_in_obj->getNextDaySchedule())) ? null : $clock_in_obj->getNextDaySchedule()->t_stamp;
            
            $clock_out_obj = new ClockOut($row,$time_in,$nextLogin,$nextDaySched);

            $time_out = $clock_out_obj->getLog();

            // if($row->dtr_date == '2025-06-21'){
            //     dd($time_out);
            // }

            if(!is_null($time_out)){
                $row->time_out_id = $time_out->line_id;
                $row->time_out = $time_out->punch_time;
            }

            // dd($row,$time_in,$nextLogin,$nextDaySched);
            $clockin_ot_obj = new ClockInOT($row,$time_in,$nextLogin,$nextDaySched);
            $clockin_ot = $clockin_ot_obj->getLog();
            
            if(!is_null($clockin_ot)){
                $row->ot_in_id = $clockin_ot->line_id;
                $row->ot_in = $clockin_ot->punch_time;
            }

            $clockout_ot_obj = new ClockOutOT($row,$time_in,$nextLogin,$nextDaySched);
            $clockout_ot = $clockout_ot_obj->getLog();
            
            if(!is_null($clockout_ot)){
                $row->ot_out_id = $clockout_ot->line_id;
                $row->ot_out = $clockout_ot->punch_time;
            }


            $new_arr = CustomRequest::filter('edtr_detailed',(array) $row);

            DB::table('edtr_detailed')
                ->where('id', $row->id)
                ->update($new_arr);

            unset($time_in);
            unset($time_out);
            unset($ot_in);
            unset($ot_out);

        }

        // $query = DB::getQueryLog();
        // dd($query);


        return $dtr;
        
    }

    public function clearDailyLogsToProcess($dtr)
    {
        foreach($dtr as $row){

            $row->time_in_id = null;
            $row->time_in = null;

            $row->time_out_id = null;
            $row->time_out = null;

            $row->ot_in_id = null;
            $row->ot_in = null;

            $row->ot_out_id = null;
            $row->ot_out = null;

            $row->ot_in_am_id = null;
            $row->ot_in_am = null;

            $row->ot_out_am_id = null;
            $row->ot_out_am = null;

            $row->ndays = 0;
            $row->hrs = 0;
            $row->awol = 0;
            
            $row->night_diff = 0;
            $row->night_diff_ot = 0;

            $row->ut = 0;
            $row->late = 0;
            $row->late_eq = 0;

            // unset($row->hol_code);
            // unset($row->sched_time_in);
            // unset($row->sched_time_out);
            // unset($row->sched_out_am);
            // unset($row->sched_in_pm);

            $new_arr = CustomRequest::filter('edtr_detailed',(array) $row);

            DB::table('edtr_detailed')
                ->where('id', $row->id)
                ->update($new_arr);
        }
    }

    public function prepareDtrRaw($emp_id)
    {
        if(!is_null($this->employee)){
            $employee = $this->employee;
        }else{
            $employee = DB::table('employees')->where('id',$emp_id)->first();
        }
       
        if($employee){
            DB::table('edtr_raw')->where('biometric_id','=',$employee->biometric_id)
                ->update(['emp_id' => $employee->id]);
        }    
     
    }

    public function raw_logs($employee,$period)
    {
        // dd($period);
        $date_from = Carbon::createFromFormat('Y-m-d',$period->date_from);
        $date_to = Carbon::createFromFormat('Y-m-d',$period->date_to)->add('1 Day');
        //select line_id,punch_date,punch_time,cstate,src,src_id,emp_id,biometric_id 
        //from edtr_raw where biometric_id =  and punch_date 

        $result = DB::table('edtr_raw') //edtr_detailed edtr_raw
            ->where('biometric_id','=',$employee->biometric_id)
            ->select('line_id','punch_date','punch_time','cstate','src','src_id','emp_id','biometric_id','new_cstate')
            ->whereBetween('punch_date',[$date_from->format('Y-m-d'),$date_to->format('Y-m-d')]);

            // dd($result->toSql(),$result->getBindings());

        return $result->orderBy('punch_date','ASC')->orderBy('punch_time','ASC')->get();
    }

    // ->where('biometric_id','=',$employee->biometric_id)

    public function regular($employee,$period)
    {
        // $holidays = $this->dtr_repo->get_holidays($period,$employee);

        // $result = DB::table('edtr_detailed') 
        //     ->leftJoinSub($holidays,'holidays',function($join){
        //         $join->on('holidays.holiday_date','=','edtr_detailed.dtr_date');
        //     })
        //     ->leftJoin('work_schedules','edtr_detailed.schedule_id','=','work_schedules.id') //N work_schedules ON edtr_detailed.schedule_id = work_schedules.id 
        //     ->where('emp_id','=',$employee->id)
        //     ->select(
        //         'edtr_detailed.id',
        //         'emp_id',
        //         'dtr_date',
        //         'edtr_detailed.time_in',
        //         'edtr_detailed.time_out',
        //         'time_in_id',
        //         'time_out_id',
        //         'ot_in',
        //         'ot_in_id',
        //         'ot_out',
        //         'ot_out_id',
        //         'hol_code',
        //         'work_schedules.time_in as sched_time_in',
        //         'work_schedules.time_out as sched_time_out'
        //     )
        //     ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

        // return $result->orderBy('dtr_date','ASC')->get();

        $result =  $this->dtr_repo->getDTR($period,$employee);

        return $result;
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
                'time_out_id',
                'ot_in',
                'ot_in_id',
                'ot_out',
                'ot_out_id'
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
                'time_out_id',
                'ot_in',
                'ot_in_id',
                'ot_out',
                'ot_out_id'
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
                'time_out_id',
                'ot_in',
                'ot_in_id',
                'ot_out',
                'ot_out_id'
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
                'time_out_id',
                'ot_in',
                'ot_in_id',
                'ot_out',
                'ot_out_id'
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
                'time_out_id',
                'ot_in',
                'ot_in_id',
                'ot_out',
                'ot_out_id'
            )
            ->whereBetween('dtr_date',[$period->date_from,$period->date_to]);

        return $result->orderBy('dtr_date','ASC')->get();
    }

    public function handleComputeRequest($emp_id,$period_id)
    {
        $employee = $this->emp_repo->getEmployee($emp_id);
        $period = $this->payperiod_repo->find($period_id);

        if(is_null($employee->location_id)){
            dd($employee);
        }

        $dtr = $this->dtr_repo->getDTR($period,$employee);

        //Act as factory
        foreach($dtr as $day){
            switch($day->hol_code){
                case 'reghol' :
                    $day = new LegalHoliday($day,$employee,$period);
                    break;

                case 'sphol' :
                    $day = new SpecialHoliday($day,$employee,$period);
                    break;

                // case 'dblreghol' :break;
                // case 'dblsphol' : break;
                
                default :
                    $day = new RegularDay($day,$employee,$period);
                    break;
            }

            $day->compute();
            // $day->save();
        }

    }

    public function handeFillOutLogs($emp_id,$period_id)
    {
        $employee = $this->emp_repo->getEmployee($emp_id);
        $period = $this->payperiod_repo->find($period_id);

        $this->dtr_repo->clearMadeLogs($period,$employee);

        $dtr = $this->dtr_repo->getLogsToFillOut($period,$employee);

        // $this->dtr_repo->fillOutLogOut($dtr);

        foreach($dtr as $row)
        {
            // dd($log)

            $ids = $this->dtr_repo->makeRawLog($row);

            $row->time_out = $row->sched_time_out;
            $row->time_out_id = $ids['time_out'];

            $row->ot_in = $row->sched_time_out;
            $row->ot_in_id = $ids['ot_in'];

            $new_arr = CustomRequest::filter('edtr_detailed',(array) $row);

            DB::table('edtr_detailed')
                ->where('id', $row->id)
                ->update($new_arr);
        }
    }

    public function handeCompletingLogs($emp_id,$period_id)
    {
        $employee = $this->emp_repo->getEmployee($emp_id);
        $period = $this->payperiod_repo->find($period_id);

        $this->dtr_repo->clearMadeCompleteLogs($period,$employee);

        $dtr = $this->dtr_repo->logsToComplete($period,$employee);

      

        foreach($dtr as $row)
        {
           
            if(is_null($row->hol_code)){
                $ids = $this->dtr_repo->makeRawLogCinCout($row);
                $row->time_in = $row->sched_time_in;
                $row->time_in_id = $ids['time_in'];

                $row->time_out = $row->sched_time_out;
                $row->time_out_id = $ids['time_out'];

                $new_arr = CustomRequest::filter('edtr_detailed',(array) $row);


                DB::table('edtr_detailed')
                    ->where('id', $row->id)
                    ->update($new_arr);
            }else{
                // $ids = $this->dtr_repo->makeRawLogCinCout($row);
                $row->time_in = null;
                $row->time_in_id = null;

                $row->time_out = null;
                $row->time_out_id = null;

                $new_arr = CustomRequest::filter('edtr_detailed',(array) $row);


                DB::table('edtr_detailed')
                    ->where('id', $row->id)
                    ->update($new_arr);
            }
           
        }
    }

    public function handleUpdateRequest($row)
    {
        $new_arr = CustomRequest::filter('edtr_detailed',$row);

        $result = DB::table('edtr_detailed')
                ->where('id', $row['id'])
                ->update($new_arr);
        
        return $result;
    }

    public function handleUpdatelogRequest($row)
    {
        $new_arr = CustomRequest::filter('edtr_raw',$row);

        $result = DB::table('edtr_raw')
                ->where('line_id', $row['line_id'])
                ->update($new_arr);
        
        return $result;
    }

}
