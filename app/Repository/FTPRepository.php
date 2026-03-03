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
        $collection = collect();

        $result = FTP::select(DB::raw("ftp_detailed.*,concat(ifnull(lastname,''),', ',IFNULL(firstname,'')) as emp_name"))
                ->leftjoin('employees','employees.id','=','ftp_detailed.emp_id')
                ->where('isChecked','=','N');

        if(!is_null($emp_id) && $emp_id != 0){
            $result->where('emp_id','=',$emp_id);
        }

        // return $result->get();

        foreach($result->get() as $item){
            if($item->time_in_date != null && $item->time_in != null){
                $flag1 = $this->checkIndB($item->time_in_date,$item->time_in,$item->emp_id,'C/In'); 
            }else{
                $flag1 = false;
            }

            if($item->time_out_date != null && $item->time_out != null){
                $flag2 = $this->checkIndB($item->time_out_date,$item->time_out,$item->emp_id,'C/Out'); 
            }else{
                $flag2 = false;
            }

            if($item->ot_in_date != null && $item->ot_in != null){
                $flag3 = $this->checkIndB($item->ot_in_date,$item->ot_in,$item->emp_id,'OT/In'); 
            }else{
                $flag3 = false;
            }

            if($item->ot_out_date != null && $item->ot_out != null){
                $flag4 = $this->checkIndB($item->ot_out_date,$item->ot_out,$item->emp_id,'OT/Out'); 
            }else{
                $flag4 = false;
            }

            if($flag1 || $flag2 || $flag3 || $flag4){
                // $item->can_approve = true;
            }else{
                // $item->can_approve = false; 
                $collection->push($item);
            }
        }

        return $collection;
    }

    public function checkIndB($punch_date,$punch_time,$emp_id,$cstate)
    {        
        $result = Logs::where('emp_id','=',$emp_id)
                ->where('punch_date','=',$punch_date)
                ->where('punch_time','=',$punch_time)
                ->where('cstate','=',$cstate)
                ->first();

        if($result){
            return true;
        }else{
            return false;
        }

    }

    public function getFTP($ftp_id)
    {
        return DB::table('ftp_detailed')
                ->where('id','=',$ftp_id)
                ->first();
    }
}
