<?php

namespace App\Repository;

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
}
