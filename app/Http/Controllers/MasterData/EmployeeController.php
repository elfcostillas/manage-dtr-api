<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Repository\EmployeeRepository;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    //

    public function __construct(public EmployeeRepository $emp_repo)
    {
        
    }

    public function list(Request $request)
    {
        $array = collect(['active','inactive']);

        $empList = $this->emp_repo->empList($array);

        return response()->json(['data' => $empList]);
    }

    public function listByLevel(Request $request)
    {
        $array = collect(['active','inactive']);
        $level = $request->level;

        $empList = $this->emp_repo->empListByRank($array,$level);

        return response()->json(['data' => $empList]);
    }
}
