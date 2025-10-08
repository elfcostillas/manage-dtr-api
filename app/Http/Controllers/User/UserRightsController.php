<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Repository\EmployeeRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Illuminate\Http\Request;

class UserRightsController extends Controller
{
    //
    public function __construct(private EmployeeRepository $emp_repo,private UserRepository $repo,private UserService $service)
    {
        
    }

    public function list(Request $request)
    {
        $result = $this->repo->getUsers();

        return $this->jsonResponse($result,null,'success');
    }

    public function  modules_list(Request $request)
    {
        $result = $this->repo->getModules();
        return $this->jsonResponse($result,null,'success');
    }

    public function saveRights(Request $request)
    {
        $data = array(
            'user' => $request->user,
            'rights' => $request->rights
        );

        $result = $this->service->handeRightsSave($data);


        return response()->json($result);
    }
    
}
