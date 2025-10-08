<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;

class UserModulesRepository
{
    //
    public function getModules($user)
    {
        // $result = $this->model->find($user->id);

         $mains = DB::table('menu_users')->select('main_id','menu_mains.label','menu_mains.icon')
            ->join('menu_subs','menu_subs.id','=','menu_users.menu_sub_id')
            ->join('menu_mains','menu_mains.id','=','menu_subs.main_id')
            ->where('menu_users.user_id','=',$user->id)
            ->distinct()
            ->get();

        foreach($mains as $main){
           // $main->items = $this->model->select('menu_subs.label','menu_subs.icon','menu_subs.path')
            $main->items = DB::table('menu_users')
                ->select('menu_subs.label','menu_subs.icon',DB::raw("concat('/',menu_subs.path) as route"))
                ->from('menu_users')
                ->join('menu_subs','menu_subs.id','=','menu_users.menu_sub_id')
                ->where('menu_users.user_id','=',$user->id)
                ->where('main_id','=',$main->main_id)
                ->get()
                ->toArray();
        }

        return $mains;
    }

    public function getUserRights($user)
    {
        $result = DB::table('menu_users')
            ->where('user_id','=',$user['id'])
            ->get();

        return $result;
    }

}
