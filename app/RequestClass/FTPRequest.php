<?php

namespace App\RequestClass;

use App\Rules\RequireDate;
use App\Rules\RequireTime;
use Carbon\Carbon;

class FTPRequest
{

    protected $id;
    protected $emp_id;
    protected $biometric_id;
    protected $ftp_date;
    protected $ftp_type;
    protected $ftp_reason;
    protected $created_by;
    protected $created_on;
    public $time_in_date;
    public $time_in;
    public $time_out_date;
    public $time_out;
    public $ot_in_date;
    public $ot_in;
    public $ot_out_date;
    public $ot_out;
    protected $ftp_status;
    protected $isChecked;

    public function rules()
    {
        return [
            'id' => 'sometimes|required',
            'emp_id' => 'required',
            'ftp_date' => 'required',
            'ftp_type' => 'required',
            'ftp_reason' => 'required',
            'time_in' => [new RequireDate($this)],
            'time_out' => [new RequireDate($this)],
            'ot_in' => [new RequireDate($this)],
            'ot_out' => [new RequireDate($this)],

            'time_in_date' => [new RequireTime($this)],
            'time_out_date' => [new RequireTime($this)],
            'ot_in_date' => [new RequireTime($this)],
            'ot_out_date' => [new RequireTime($this)],
        ];
    }

    public function __construct($array)
    {
        foreach($array as $key => $value){
            $this->{$key} = $value;
        }

        $this->time_in_date = $this->formatDate($array['time_in_date']);
        $this->time_out_date = $this->formatDate($array['time_out_date']);
        $this->ot_in_date = $this->formatDate($array['ot_in_date']);
        $this->ot_out_date = $this->formatDate($array['ot_out_date']);
        $this->ftp_date = $this->formatDate($array['ftp_date']);

        $this->ftp_status = (is_null($array['ftp_status'])) ? 'Draft' : $array['ftp_status'] ;
    }

    public function save()
    {

    }

    public function getArray()
    {
        return get_object_vars ($this);
        
    }

    protected function formatDate($date)
    {

        return ($date) ? Carbon::createFromFormat('m/d/Y',$date)->format('Y-m-d') : null;
    }

}
