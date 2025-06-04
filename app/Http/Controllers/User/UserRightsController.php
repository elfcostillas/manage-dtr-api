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

    
}
