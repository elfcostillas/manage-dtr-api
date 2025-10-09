<?php

namespace App\Repository;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    //
    public function getUsers()
    {
        $result = DB::table('users')
                    ->select('id','name','email','super_user')
                    ->orderBy('id','asc')
                    ->get();
        
        return $result;

    }

    public function getModules()
    {
       
        // select * from menu_subs;
        $result = DB::table('menu_subs')->get();

        foreach($result as $row)
        {
            $main = DB::table('menu_mains')
                ->where('id','=',$row->main_id)
                ->select('id','label')
                ->first();

            $row->main = $main;
        }

        return $result;
    }

    public function showRights($user)
    {
        // $result = DB::table('menu_users')
        //     ->where('user_id',$user['id'])
        //     ->get();

        // $rights = [];

        // foreach($result as $r)
        // {
        //     array_push($rights,$r->menu_sub_id);
        // }
        
        // return $rights;

        /*
        select menu_subs.* from menu_subs 
        inner join menu_users on menu_subs.id = menu_users.menu_sub_id
        where menu_users.user_id = 1

        */

        $result = DB::table('menu_subs')
                ->join('menu_users','menu_subs.id','=','menu_users.menu_sub_id')
                ->select(DB::raw("menu_subs.*"))
                ->where('menu_users.user_id','=',$user['id'])
                ->get();
            
        return $result;
    }
}
