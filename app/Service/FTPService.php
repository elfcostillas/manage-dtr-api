<?php

namespace App\Service;

use App\CustomClass\ErrorMessage;
use App\Models\Timekeeping\FTP;
use App\RequestClass\FTPRequest;
use Illuminate\Support\Facades\Validator;

class FTPService
{
    //
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
        $result = FTP::where('id','=',$requestData['id'])
                ->where('isChecked','=','N')
                ->update([
                    'isChecked' => 'Y',
                    'checked_by' => $user->id,
                    'checked_on' => now(),
                ]);
        
        return $result;
    }


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
