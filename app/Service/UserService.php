<?php

namespace App\Service;

class UserService
{
    //

    public function handeRightsSave($data)
    {
        $rights_arr = [];

        $user = $data['user'];
        $rights = $data['rights'];
    

        foreach($rights as $right){
            // return $right;
            array_push($rights_arr,$right['id']);
        }

        return $rights_arr;
    }
}
