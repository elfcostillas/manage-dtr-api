<?php

namespace App\CustomClass;

use Illuminate\Support\Facades\Schema;

class CustomRequest
{
    //
    public static function filter($table_name,$array){
        $columns = Schema::getColumnListing($table_name);
       
        foreach($array as $key => $value){
            if(!in_array($key,$columns)){
                unset($array[$key]);
            }
        }

        return $array;
    }
}
