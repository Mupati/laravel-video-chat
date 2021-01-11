<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    private function generateToken($request)
    {
        $token = $request->user()->createToken('wossop_token');
        return ['token' => $token->plainTextToken];
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required'],
            'password' => ['required']
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            Log::info('type: Login, user:' . $request->email . ', datetime:' . now()->toDateTimeString() . ', client_ip: ' . $request->getClientIp());

            $token = $this->generateToken($request)['token'];

            // Update login time and IP Address used.
            Auth::user()->update([
                'last_login_at' => now()->toDateTimeString(),
                'last_login_ip' => $request->getClientIp()
            ]);

            return response()->json(['user' => Auth::user(), 'token' => $token], 200);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.']
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed']
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        Log::info('type: registration, user:' . $request->email . ', status: account_created');

        return response()->json(['success' => true, 'message' => 'Your account has been successfully created'], 201);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();

        Log::info('type: Logout, user:' . Auth::user()->email . ', datetime:' . now()->toDateTimeString());

        return response()->json(['success' => true, "message" => "Logout successfull"], 200);
    }

    public function fetchAuthUser()
    {
        return Auth::user();
    }

    public function fetchAllUsers()
    {
        return User::where('id', '!=', Auth::id())->get();
    }
}
