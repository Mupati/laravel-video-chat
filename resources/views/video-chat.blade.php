@extends('layouts.app')

@section('content')
	<video-chat :allusers="{{ $users }}" :authUserId="{{ auth()->id() }}"
    	turn_url="{{ env('TURN_SERVER_URL') }}" />


@endsection
