@extends('emails.layouts.html.index')

@section('content')
	<p>
		Hi!
	</p>
	<p>
		Your new password: {{$password}}
	</p>
@stop