<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Classes\AgoraDynamicKey\RtcTokenBuilder;
use App\Events\MakeAgoraCall;

class AgoraVideoController extends Controller
{
    public function index(Request $request)
    {
        // fetch all users apart from the authenticated user
        $users = User::where('id', '<>', Auth::id())->get();
        return view('agora-chat', ['users' => $users]);
    }

    protected function token(Request $request)
    {

        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        // $channelName = $channel;   //env('AGORA_APP_CHANNEL_NAME');
        $channelName = $request->channelName;
        $user = Auth::user()->name;
        $uid = 2882341273;
        $uidStr = "2882341273";
        $role = RtcTokenBuilder::RoleAttendee;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = now()->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        // $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
        // echo 'Token with int uid: ' . $token . PHP_EOL;

        $token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $user, $role, $privilegeExpiredTs);
        // echo 'Token with user account: ' . $privilegeExpiredTs . ',' . $token . PHP_EOL;


        return $token;
    }

    public function callUser(Request $request)
    {

        $data['userToCall'] = $request->user_to_call;
        $data['channelName'] = $request->channel_name;
        $data['from'] = Auth::id();
        $data['type'] = 'incomingCall';

        broadcast(new MakeAgoraCall($data))->toOthers();

        // $token = $this->token($request->channel_name);
        // return response()->json(['token' => $token], 200);
    }

    // public function acceptCall(Request $request)
    // {
    //     $data['channelName'] = $request->channelName;
    //     $data['to'] = $request->to;
    //     $data['type'] = 'callAccepted';

    //     broadcast(new MakeAgoraCall($data))->toOthers();

    //     $token = $this->token($request->channel_name);
    //     return response()->json(['token' => $token], 200);
    // }
}
