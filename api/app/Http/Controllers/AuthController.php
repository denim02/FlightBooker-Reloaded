<?php

namespace App\Http\Controllers;
use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            \Log::info('Hi', [$validator->errors()]);
            return ApiResponse::validationFailed($validator->errors());
        }

        if (
            !\Auth::attempt([
                'email' => $request->email,
                'password' => $request->password
            ], true)
        ) {
            return ApiResponse::validationFailed([
                'email' => 'Invalid email or password. Please try again'
            ]);
        }

        $request->session()->regenerate();
        return ApiResponse::noContent();
    }

    public function logout(Request $request)
    {
        \Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ApiResponse::noContent();
    }

    public function register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::min(8)->max(16)->letters()->mixedCase()->numbers()],
            'phone_number' => ['required', 'string', 'max:20']
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationFailed($validator->errors());
        }

        $user = User::create([
            'name' => "{$request->first_name} {$request->last_name}",
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => \Hash::make($request->string('password')),
        ]);

        // event(new Registered($user));

        \Auth::login($user, true);

        return ApiResponse::noContent();
    }

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }

    public function verifyEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url') . '/dashboard?verified=1'
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(
            config('app.frontend_url') . '/dashboard?verified=1'
        );
    }
}
