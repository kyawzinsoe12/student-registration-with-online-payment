<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'password' => ['required', Password::defaults()],
        ]);

        $validated['email'] = strtolower($validated['email']);
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->success(
            'User registered successfully.',
            [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            201
        );
    }

    /**
     * Login user.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials['email'] = strtolower($credentials['email']);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        $user = Auth::user();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->success(
            'Login successful.',
            [
                'user' => new UserResource($user),
                'token' => $token,
            ]
        );
    }

    /**
     * Send password reset OTP.
     */
    public function verifyResetPasswordOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $validated['email'] = strtolower($validated['email']);

            $user = User::where('email', $validated['email'])->first();

            $otp = random_int(100000, 999999);

            Cache::put(
                'rest_otp_' . $user->email,
                $otp,
                now()->addMinutes(5)
            );

            Mail::raw(
                "Your password reset OTP is: {$otp}",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Password Reset OTP');
                }
            );

            return $this->success(
                'OTP sent successfully.',
                [
                    // Remove this in production
                    'otp' => $otp,
                ]
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Validation failed.',
                $e->errors(),
                422
            );
        } catch (Exception $e) {
            return $this->error(
                'Something went wrong.',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|digits:6',
                'password' => ['required', Password::defaults()],
            ]);

            $validated['email'] = strtolower($validated['email']);

            $user = User::where('email', $validated['email'])->first();

            $cachedOtp = Cache::get('rest_otp_' . $validated['email']);

            if (!$cachedOtp || $cachedOtp != $validated['otp']) {
                throw ValidationException::withMessages([
                    'otp' => ['Invalid or expired OTP.'],
                ]);
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            Cache::forget('rest_otp_' . $validated['email']);

            // Logout from all devices
            $user->tokens()->delete();

            return $this->success(
                'Password reset successfully.'
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Validation failed.',
                $e->errors(),
                422
            );
        } catch (Exception $e) {
            return $this->error(
                'Something went wrong.',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Logout current user.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->currentAccessToken()) {
            return $this->error(
                'Unauthorized or token not found.',
                null,
                401
            );
        }

        $user->currentAccessToken()->delete();

        return $this->success(
            'Logout successfully.'
        );
    }
}
