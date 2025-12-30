<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use Illuminate\Container\Attributes\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
// use League\Config\Exception\ValidationException;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated  = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'string|max:12',
            'address'=>'required|string|max:255',
            'password' => ['required','string','confirmed', Password::min(8)],
        ]);
        $users = User::create([
            'name'  =>  $validated['name'],
            'email' =>  $validated['email'],
            'phone' =>  $validated['phone'] ?? null,
            'address'   => $validated['address'] ?? null,
            'password' => Hash::make($validated['password'])
        ]);
        
        return response()->json([
            'users' => $users,
            'status'=> 'success',
            'message' => 'user created successfully'
        ],201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' =>  'required|email',
            'password' => ['required','string', Password::min(8)
        ]);

        $credentials = $request->only('email','password');

        if(!Auth::attempt($credentials)){
            throw ValidationException::withMessages([
                'emial' =>  ['Invalid email or password'],
            ]);
        }

        $user = User::where('email',$request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            "status" => "Success",
            "message" => "login successfully",
            "token" => $token,
        ],200);
    }

}
