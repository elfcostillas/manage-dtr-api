<?php

namespace App\CustomClass;

class ErrorMessage
{
    //
    public function friendlyMessage($e)
    {
        switch($e->getCode()){
            default :
                return $e->getMessage();
            break;
        }
    }
}
