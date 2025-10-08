<?php

namespace App\Http\Controllers\Timekeeping;

use App\Http\Controllers\Controller;
use App\Repository\FTPRepository;
use App\Service\FTPService;
use Illuminate\Http\Request;

class FTPController extends Controller
{
    // 

    public function __construct(private FTPRepository $ftp_repo,private FTPService $service)
    {
        
    }

    public function list(Request $request)
    {
        $emp_id = $request->emp_id;

        $result = $this->ftp_repo->list($emp_id);

        return $this->jsonResponse($result,null,'success');
    }

    public function store(Request $request)
    {
        $result = $this->service->handle($request->all());
        // return $this->json($result,'Success','success');
        if(method_exists($result,'getMessage')){
            return $this->json($result,$result->getMessage(),'error');
        }else{
            return $this->json($result,'Success','success');
        }

        // if(is_object($result)){
        //     return $this->json($result,$result->getMessage(),'error');
        // }else{
        //     return $this->json($result,'Success','success');
        // }
    }
}
