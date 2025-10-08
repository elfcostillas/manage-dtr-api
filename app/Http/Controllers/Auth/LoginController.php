<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if (!auth()->attempt($request->only(['email', 'password']))) 
        // if(!$user || !Hash::check($request->password,$user->password) )
        {
            throw ValidationException::withMessages([
                'email' => 'Credintials are incorrect.'
            ]);
        }
    }
}
