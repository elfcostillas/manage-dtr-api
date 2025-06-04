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
    public function __construct(EmployeeRepository $emp_repo,UserRepository $repo,UserService $service)
    {
        
    }

    
}
