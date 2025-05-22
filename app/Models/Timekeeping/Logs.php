<?php

namespace App\Models\Timekeeping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory;

    protected $primaryKey = 'line_id';
    protected $table = 'edtr_raw';
    public $timestamps = false;

    protected $fillable = [
        'punch_date',
        'punch_time',
        'biometric_id',
        'cstate',
        'src',
        'src_id',
    ];

}
