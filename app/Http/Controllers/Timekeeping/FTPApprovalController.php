<?php

namespace App\Http\Controllers\Timekeeping;

use App\Http\Controllers\Controller;
use App\Repository\FTPRepository;
use App\Service\FTPService;
use Illuminate\Http\Request;

class FTPApprovalController extends Controller
{
    //

    public function __construct(private FTPRepository $ftp_repo,private FTPService $service)
    {
        
    }

    public function list(Request $request)
    {
        $emp_id = $request->emp_id;
        $result = $this->ftp_repo->forApprovalList($emp_id);
        return $this->jsonResponse($result,null,'success');
    }

    public function approve(Request $request)
    {
        $result = $this->service->handleApproveRequest($request->all(),$request->user());
        return $this->json($result,null,'success');
    }
}
