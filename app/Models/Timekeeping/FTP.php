<?php

namespace App\Models\Timekeeping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FTP extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    protected $table = 'ftp_detailed';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'emp_id',
        'biometric_id',
        'ftp_date',
        'ftp_type',
        'ftp_reason',
        'created_by',
        'created_on',
        'time_in_date',
        'time_in',
        'time_out_date',
        'time_out',
        'ot_in_date',
        'ot_in',
        'ot_out_date',
        'ot_out',
        'ftp_status',
        'isChecked',
        'checked_by',
        'checked_on'
    ];


}
