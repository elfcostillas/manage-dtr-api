<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;

class EmployeeRepository
{
    //
    public function empList($array)
    {
        $list = [];

        foreach($array as $type)
        {
            $result = DB::table('employees')
                ->select(DB::raw("id, biometric_id,lastname,firstname,concat(ifnull(lastname,''),', ',ifnull(firstname,'')) as emp_name,concat(ifnull(lastname,''),', ',ifnull(firstname,'')) as label"))
                ->where('job_title_id','!=',130)
                ->where('emp_level','<',6)
                ->orderBy('lastname','asc')
                ->orderBy('firstname','asc');
              
                if($type == 'active'){
                    $result->where('exit_status',1);
                }else{
                    $result->where('exit_status','!=',1);
                }
            $list[$type] = $result->get();
            // $type->list = $result->get();
            
        }

        return $list;
    }

    public function empListByRank($array,$emp_level)
    {
         $list = [];

        foreach($array as $type)
        {
            $result = DB::table('employees')
                ->select(DB::raw("id, biometric_id,lastname,firstname,concat(ifnull(lastname,''),', ',ifnull(firstname,'')) as emp_name,concat(ifnull(lastname,''),', ',ifnull(firstname,'')) as label"))
                ->where('job_title_id','!=',130)
                ->orderBy('lastname','asc')
                ->orderBy('firstname','asc');

                if($emp_level == 5){
                    $result = $result->where('emp_level','=',5);
                }

                if($emp_level > 5){
                    $result = $result->where('emp_level','>',5);
                }

                if($emp_level < 5){
                    $result = $result->where('emp_level','<',5);
                }

                if($type == 'active'){
                    $result->where('exit_status',1);
                }else{
                    $result->where('exit_status','!=',1);
                }
            $list[$type] = $result->get();
            // $type->list = $result->get();
            
        }

        return $list;
    }

    public function employeeActiveSemiMonthly()
    {
        $result = DB::table('employees')
            ->where('exit_status',1)
            ->where('emp_level','<',6)
            ->get();
        
        return $result;
    }

    public function employeeActiveSG()
    {
        $result = DB::table('employees')
            ->where('exit_status',1)
            ->where('emp_level','=',6)
            ->get();
        
        return $result;
    }

    public function getEmployee($emp_id)
    {
        return DB::table('employees')
            ->where('id','=',$emp_id)
            ->first();
    }
}

/*

select id, biometric_id,lastname,firstname,concat(ifnull(lastname,''),', ',ifnull(firstname,'')) as emp_name
from employees where exit_status = 1 
and job_title_id != 130
order by lastname,firstname asc;

*/