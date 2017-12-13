@extends('emails.layouts.index')

@section('content')
	<p>
		Hi!
	</p>
	<p>
		Your <a href="{{config('frontend.protocol')}}://{{config('frontend.domain')}}/email/change/{{$token}}">link</a> for change password.
	</p>
@stop