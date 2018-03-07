@extends('emails.layouts.html.index')

@section('content')
	<p>Dear client{{$user->name ? ', ' . $user->name : ''}}!</p>
	<p>
		We are happy to announce that your accont was activated and your email is verified in our system.<br/>
		For full verification of your account, please login to your backoffice and upload the required documents in your profile.
	</p>
	<p>If you have any additional questions, feel free to contact our support team</p>
	<p>Looking forward the cooperation</p>
@stop