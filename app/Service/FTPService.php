<?php

namespace App\Service;

use App\CustomClass\ErrorMessage;
use App\Models\Timekeeping\FTP;
use App\Repository\EmployeeRepository;
use App\Repository\FTPRepository;
use App\RequestClass\FTPRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FTPService
{
    //
    public function __construct(private EmployeeRepository $emp_repo, private FTPRepository $ftp_repo)
    {

    }

    public function handle($request)
    {
        $request_obj = new FTPRequest($request);

        $errMsg = new ErrorMessage();

        $array = $request_obj->getArray();

        if(array_key_exists('id',$array) && is_null($array['id'])){
            unset($array['id']);
        }

        $validator = Validator::make($array,$request_obj->rules());

        if ($validator->fails()) {
            return $validator->errors();
        }  

        $validated = $validator->validated();

        if(array_key_exists('id',$validated)){
            try {
               
                $result = FTP::where('id','=',$validated['id'])->update($validated);
              
            } catch (\Exception $e) {
                // return $errMsg->friendlyMessage($e);
                return $e;
            }
           
        }else{
            
            try {
                $result = FTP::create($validated);
            } catch (\Exception $e) {
                // return $errMsg->friendlyMessage($e);
                return $e;
            }
        }

        return $result;
       
    }

    public function handleApproveRequest($requestData,$user)
    {
        $employee = $this->emp_repo->getEmployee($requestData['emp_id']);
        
        $result = FTP::where('id','=',$requestData['id'])
            ->where('isChecked','=','N')
            ->update([
                'isChecked' => 'Y',
                'checked_by' => $user->id,
                'checked_on' => now(),
            ]);

        $ftp_id = $requestData['id'];

        $result = $this->makeRawLogs($ftp_id,$employee);
        
        return $result;
        
    }

    public function makeRawLogs($ftp_id,$employee)
    {
        $ftp = $this->ftp_repo->getFTP($ftp_id);
        
        if(!is_null($ftp->time_in)){ 
            $time_in_array = [
                'punch_date' => $ftp->time_in_date,
                'punch_time' => $ftp->time_in,
                'biometric_id' => $employee->biometric_id,
                'cstate' => 'C/In',
                'src' => 'ftp',
                'src_id' => $ftp_id,
                'emp_id' => $employee->id,
                'new_cstate' => null,
            ];

            DB::table('edtr_raw')->insert($time_in_array);
    
        };

        if(!is_null($ftp->time_out)){ 
            $time_out_array = [
                'punch_date' => $ftp->time_out_date,
                'punch_time' => $ftp->time_out,
                'biometric_id' => $employee->biometric_id,
                'cstate' => 'C/Out',
                'src' => 'ftp',
                'src_id' => $ftp_id,
                'emp_id' => $employee->id,
                'new_cstate' => null,
            ];
            DB::table('edtr_raw')->insert($time_out_array);
        };

        if(!is_null($ftp->ot_in)){ 
            $time_in_ot_array = [
                'punch_date' => $ftp->ot_in_date,
                'punch_time' => $ftp->ot_in,
                'biometric_id' => $employee->biometric_id,
                'cstate' => 'OT/In',
                'src' => 'ftp',
                'src_id' => $ftp_id,
                'emp_id' => $employee->id,
                'new_cstate' => null,
            ];
            DB::table('edtr_raw')->insert($time_in_ot_array);
        };

        if(!is_null($ftp->ot_out)){ 
            $time_out_ot_array = [
                'punch_date' => $ftp->ot_out_date,
                'punch_time' => $ftp->ot_out,
                'biometric_id' => $employee->biometric_id,
                'cstate' => 'OT/Out',
                'src' => 'ftp',
                'src_id' => $ftp_id,
                'emp_id' => $employee->id,
                'new_cstate' => null,
            ];
            DB::table('time_out_ot_array')->insert($time_in_ot_array);
        };

    
    }

    /*

    "data": {
        "id": 6,
        "emp_id": 484,
        "biometric_id": null,
        "ftp_date": "2025-04-03",
        "ftp_type": "OB",
        "ftp_reason": "test",
        "created_by": null,
        "created_on": null,

        "time_in_date": null,
        "time_in": null,

        "time_out_date": "2025-04-03",
        "time_out": "17:00",

        "ot_in_date": null,
        "ot_in": null,

        "ot_out_date": null,
        "ot_out": null,

        "ftp_status": null,
        "isChecked": "N",
        "checked_by": null,
        "checked_on": null
    },

    punch_date
    punch_time
    biometric_id
    cstate
    src
    src_id
    emp_id
    new_cstate
    */


}

/*
 public function getException($action, $e = null)
    {
        $message = '';
        $tmp = 'error';
        $errMsg = new ErrorMessage();
        switch ($action) {
            case 'create':
                // $message = empty($this->exceptions[$action]) ?
                //     'Unable to add new data.<br><br>Details: ' . (empty($e) ?
                //         '' : ' ' . $e->getMessage()) : $this->exceptions[$action];
                $message = $errMsg->friendlyMsg($e->getCode(), $action, $e);
                break;
            case 'update':
                $message = empty($this->exceptions[$action]) ?
                    'Unable to update data.<br><br>Details: ' . (empty($e) ?
                        '' : ' ' . $e->getMessage()) : $this->exceptions[$action];
                break;
            case 'destroy':
                $message = 'Unable to delete data.<br><br>Details: ';
                if (empty($this->exceptions[$action])) {
                    if (!empty($e)) {
                        if ($e->getCode() == 23503) {
                            $message .= 'Still used by other modules.';
                        } else {
                            $message .= $e->getMessage();
                        }
                    }
                } else {
                    $message .= $this->exceptions[$action];
                }
                $tmp = 'delete_error';
                break;
        }
        return (object) array($tmp => array('messages' => $message));
    }
*/
