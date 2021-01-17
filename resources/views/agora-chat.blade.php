@extends('layouts.app')

@section('scripts')
    <script src="https://cdn.agora.io/sdk/release/AgoraRTCSDK-3.3.1.js"></script>
@endsection

@section('content')
    <agora-chat :allusers="{{ $users }}" authuserid="{{ auth()->id() }}" authuser="{{ auth()->user()->name }}"
        agora_id="{{ env('AGORA_APP_ID') }}" />
@endsection
