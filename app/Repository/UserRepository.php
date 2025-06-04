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
}
