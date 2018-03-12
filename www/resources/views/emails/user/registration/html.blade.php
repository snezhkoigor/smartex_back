@extends('emails.layouts.html.index')

@section('content')
<p>
	Dear client{{$user->name ? ', ' . $user->name : ''}}!
</p>
<p>
	We are happy to confirm your registration on {{config('app.name')}}.
</p>
<p>
	E-mail: {{$user->email}}<br/>
	Password: {{$password}}
</p>
<p>
	Looking forward the future cooperation.<br/>
	If you have any questions, do not hesitate to contact us on our contact email through the online support chat on our website.
</p>
<p>
	To complete the registration please follow the link below and confirm your e-mail address:<br/>
	<a href='{{config('app.website_url') . '/user/activation/' . md5($user->email)}}'>{{config('app.website_url') . '/user/activation/' . md5($user->email)}}</a>
</p>
@stop