<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Events\UpdateLoginTime;


use App\Models\User;
use App\Models\WossopMessage;

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
        // TODO: when a new user registers, you can broadcast their details so that they are added to the 
        // allUsers fetched on the frontend
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

        // Update login time and IP Address used.
        // This is because when a user logs out, the time they log out
        // becomes their last login time
        $user->update([
            'last_login_at' => now()->toDateTimeString(),
        ]);
        $user->tokens()->delete();

        Log::info('type: Logout, user:' . Auth::user()->email . ', datetime:' . now()->toDateTimeString());

        // Broadcast your last login at
        broadcast(new UpdateLoginTime(['id' => $user->id, 'last_login_at' => $user->last_login_at]))->toOthers();

        return response()->json(['success' => true, "message" => "Logout successful"], 200);
    }

    public function fetchAuthUser()
    {
        return Auth::user();
    }

    public function fetchAllUsers()
    {
        $users =  User::where('id', '!=', Auth::id())->get();
        $results["users"] = $users;
        foreach ($users as $key => $user) {
            // fetch users along with the latest message sent or received by them and the authenticated user
            $users[$key]['latest_message'] = WossopMessage::select(['message', 'created_at'])
                ->where(function ($query) use ($user) {
                    $query->where('receiver', $user->id)
                        ->orWhere('sender', $user->id);
                })
                ->where(function ($query) {
                    $query->where('receiver', Auth::id())
                        ->orWhere('sender', Auth::id());
                })
                ->orderByDesc('id')->limit(1)->get();
        }

        return $users;
    }
}
