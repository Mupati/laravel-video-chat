<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Events\SendWossopMessage;
use App\Models\WossopMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WossopMessageController extends Controller
{

    /**
     * Send new WossopMessage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function sendMessage(Request $request)
    {
        $receiver = $request->receiver_id;
        $sender = Auth::id();
        $message = $request->message;

        $new_message = new WossopMessage();
        $new_message->sender = $sender;
        $new_message->receiver = $receiver;
        $new_message->message = $message;
        $new_message->is_read = 0;

        $new_message->save();

        // $data = ['sender' => $sender, 'receiver' => $receiver, 'message' => $message];


        event(new SendWossopMessage($new_message));
    }


    /**
     * Fetch messages sent and received by authenticated user from a particular user
     * @param user_id the id of the user in the chatlist
     * @return WossopMessage
     */
    public function fetchUserMessages($user_id)
    {
        $auth_user_id = Auth::id();

        // If message sent to authenticated user is clicked, set 'is_read' to 1
        WossopMessage::where(['sender' => $user_id, 'receiver' => $auth_user_id])->update(['is_read' => 1]);


        $messages =  WossopMessage::where(function ($query) use ($user_id, $auth_user_id) {
            $query->where('sender', $user_id)->where('receiver', $auth_user_id);
        })->orWhere(function ($query) use ($user_id, $auth_user_id) {
            $query->where('sender', $auth_user_id)->where('receiver', $user_id);
        })->get();
        return $messages;
    }
}
