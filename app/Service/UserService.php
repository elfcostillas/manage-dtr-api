<?php

namespace App\Service;

use App\Repository\UserModulesRepository;
use Illuminate\Support\Facades\DB;

class UserService
{
    //
    public function __construct(private UserModulesRepository $usermod_repo)
    {
        
    }

    public function handeRightsSave($data)
    {
        $rights_arr = [];
        $rights_arr_db = [];

        $user = $data['user'];
        $rights = $data['rights'];
        
        if(!is_null($rights)){
            if(count($rights) > 0){
                foreach($rights as $right){
                    array_push($rights_arr,$right['id']);
                }
            }
        }

        /* 
            1. get the rights in the database
            2. loop through rights in db, compare the ritghs if it is in the $rights_arr
                -if true, dont delete and store in $rights_arr_db
                -if false, delete
            3. loop through $rights_arr,  compare to $rights_arr_db
                - if true, do nothing
                - if false, insert into db
        */
        
        // $user['id'];
        $rigths_in_db = $this->usermod_repo->getUserRights($user);
       
        if($rigths_in_db->count() > 0){
            foreach($rigths_in_db as $row)
            {
                // put looped rights into array
                array_push($rights_arr_db,$row->menu_sub_id);
                if(!in_array($row->menu_sub_id,$rights_arr)){
                    DB::table('menu_users')->where('id','=',$row->id)->delete();
                }
            }
        }

        // return $ritghs_in_db;

        foreach($rights_arr as $key => $value){
            // return $value;
            if(!in_array($value,$rights_arr_db)){
                $tmp_array = [
                    'user_id' => $user['id'],
                    'menu_sub_id' => $value 

                ];

                DB::table('menu_users')->insert($tmp_array);
            }
        } 

        return $rights_arr;

        return true;

    }
}