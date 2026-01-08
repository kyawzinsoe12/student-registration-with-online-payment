<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use Illuminate\Container\Attributes\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
// use League\Config\Exception\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password;
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
            'password' => ['required','string', Password::min(8)],
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
            'password' => ['required','string', Password::min(8)],
        ]);

        $credentials = $request->only('email','password');

        if(!Auth::attempt($credentials)){
            throw ValidationException::withMessages([
                'email' =>  ['Invalid email or password'],
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

    public function verifyResetPasswordOtp(Request $request)
    {
        try{
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email',$request->email)->first();

            $putOtp = random_int(100000,999999);

            Cache::put('rest_otp'.$user->email,$putOtp,now()->addMinutes(5));

            Mail::raw("Your password reset OTP is: $putOtp", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Password Reset OTP');
            });

            return response()->json([
                "status" => "success",
                "message" => "otp send successfully",
                "otp" => $putOtp,
            ],200);
            
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                "status" => "error",
                "message" => $e->errors(),
            ],422);
        }catch(Exception $e){
            return response()->json([
                "status" => "error",
                "message" =>$e->getMessage(),
            ],500);
        }
        
    }
    public function resetPassword(Request $request)
    {
        try{
            $request->validate([
                'otp' => 'required|digits:6',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:8',
            ]);

            $user = User::where('email',$request->email)->first();
            $catchOtp = Cache::get('rest_otp'. $request->email);

            if(!$user || !$catchOtp || $catchOtp!=$request->otp){
                throw ValidationException::withMessages([
                    'otp' => ['Invalid Otp or not a valid email! '],
                ]);
            }

            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();

            Cache::forget('catch_otp'.$request->email);
            $user->tokens()->delete();

            return response()->json([
                "status" => "success",
                "message" => "reset password successfully",
            ],200);
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                "status" => "error",
                "message" => $e->errors(),
            ],422);
        }catch(Exception $e){
            return response()->json([
                "status" => "error",
                "message" =>$e->getMessage(),
            ],500);
        }
    }

}
