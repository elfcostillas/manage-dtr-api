<?php

namespace App\Repository;

use App\Models\Timekeeping\FTP;
use App\Models\Timekeeping\Logs;
use Illuminate\Support\Facades\DB;

class FTPRepository
{
   
    public function list($emp_id)
    {
        $result = FTP::select(DB::raw("ftp_detailed.*,concat(ifnull(lastname,''),', ',IFNULL(firstname,'')) as emp_name"))
                ->leftjoin('employees','employees.id','=','ftp_detailed.emp_id')
                ->where('isChecked','=','N');

        if(!is_null($emp_id) && $emp_id != 0){
            $result->where('emp_id','=',$emp_id);
        }

        return $result->get();
    }

    public function forApprovalList($emp_id)
    {
        $result = FTP::select(DB::raw("ftp_detailed.*,concat(ifnull(lastname,''),', ',IFNULL(firstname,'')) as emp_name"))
                ->leftjoin('employees','employees.id','=','ftp_detailed.emp_id')
                ->where('isChecked','=','N');

        if(!is_null($emp_id) && $emp_id != 0){
            $result->where('emp_id','=',$emp_id);
        }

        return $result->get();
    }
}
