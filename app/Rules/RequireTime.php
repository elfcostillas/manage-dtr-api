<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequireTime implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    public function __construct(public $obj)
    {
        
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
        if($attribute =='time_in_date')
        {
            if(!is_null($value) && is_null($this->obj->time_in)){
                $fail('Time in is required');
            }
        }

        if($attribute =='time_out_date')
        {
            if(!is_null($value) && is_null($this->obj->time_out)){
                $fail('Time out is required');
            }
        }

        if($attribute =='ot_in_date')
        {
            if(!is_null($value) && is_null($this->obj->ot_in)){
                $fail('Overtime time in is required');
            }
        }

        if($attribute =='ot_out_date')
        {
            if(!is_null($value) && is_null($this->obj->ot_out)){
                $fail('Overtime time out is required');
            }
        }
    }
}
