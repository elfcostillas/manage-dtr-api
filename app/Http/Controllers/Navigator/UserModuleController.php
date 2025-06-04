<?php

namespace App\Http\Controllers\Navigator;

use App\Http\Controllers\Controller;
use App\Models\Navigator\MainModule;
use App\Models\User;
use App\Repository\UserModulesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserModuleController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function __invoke(Request $request,UserModulesRepository $mapper)
    {
        //
        $modules = $mapper->getModules($request->user());
        
        // dd($request->user());
        return response()->json([
            'user' => $request->user(),
            'modules' => $modules
        ]);
    }
}
