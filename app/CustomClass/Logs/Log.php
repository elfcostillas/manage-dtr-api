<?php

namespace App\CustomClass\Logs;

use Illuminate\Support\Facades\DB;

class Log
{
    //
    public $data;

    public $log;

    public function __construct($data)
    {

        $this->data = $data;
        $this->buildSelf();
  
    }






}

///SELECT * FROM edtr_raw_vw WHERE punch_date biometric_id cstate