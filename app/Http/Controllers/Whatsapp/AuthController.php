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
use Illuminate\Support\Facades\DB;

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

    // Fetching Users
    public function fetchAuthUser()
    {
        return Auth::user();
    }

    public function fetchAllUsers()
    {
        return User::all();
    }

    public function fetchContactedUsers()
    {
        $authUser = Auth::id();

        // Fetch the users that have contacted the authenticated user and the number of unread messages.
        // Add the last message sent or received as well.

        return  DB::select(DB::raw("
        SELECT mr2.*, mr.message, mr.created_at 
        FROM 
            (SELECT r1.id, r1.latest_message_id, wm1.message, wm1.created_at 
            FROM wossop_messages wm1
            JOIN
                (SELECT u.id, u.name, MAX(wm.id) latest_message_id
                FROM users u 
                JOIN wossop_messages wm
                    ON (u.id = wm.receiver AND wm.sender = '$authUser') OR (u.id = wm.sender AND wm.receiver = '$authUser')
                GROUP BY u.id, u.name) AS r1
            ON wm1.id = r1.latest_message_id) AS mr
        JOIN
            (SELECT u.id, u.name, u.email, u.last_login_at, COUNT(CASE WHEN wm.receiver = '$authUser' AND wm.is_read = 0 THEN 1 END) AS unread_count 
            FROM users u 
            JOIN wossop_messages wm
                ON (u.id = wm.receiver AND wm.sender = '$authUser') OR (u.id = wm.sender AND wm.receiver = '$authUser')
            GROUP BY u.id, u.name, u.email, u.last_login_at) AS mr2
        ON mr.id = mr2.id
        ORDER BY mr.latest_message_id DESC;
        "));
    }
}
