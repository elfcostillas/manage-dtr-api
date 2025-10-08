<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequireDate implements ValidationRule
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
      
        if($attribute == 'time_in')
        {
            if(!is_null($value) && is_null($this->obj->time_in_date)){
                $fail('Time in Date is required');
            }
        }

        if($attribute == 'time_out')
        {
            if(!is_null($value) && is_null($this->obj->time_out_date)){
                $fail('Time out Date is required');
            }
        }

        if($attribute == 'ot_in')
        {
            if(!is_null($value) && is_null($this->obj->ot_in_date)){
                $fail('Overtime time in Date is required');
            }
        }

        if($attribute == 'ot_out')
        {
            if(!is_null($value) && is_null($this->obj->ot_out_date)){
                $fail('Overtime time out Date is required');
            }
        }
  
    }
}
