@extends('emails.layouts.plain.index')

@section('content')
Dear client{{$user->name ? ', ' . $user->name : ''}}!

We are happy to announce that your accont was activated and your email is verified in our system.
For full verification of your account, please login to your backoffice and upload the required documents in your profile.

If you have any additional questions, feel free to contact our support team
Looking forward the cooperation
@stop