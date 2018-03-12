@extends('emails.layouts.html.index')

@section('content')
Dear client{{$user->name ? ', ' . $user->name : ''}}!

We are happy to confirm your registration on {{config('app.name')}}.

E-mail: {{$user->email}}<br/>
Password: {{$password}}

Looking forward the future cooperation.
If you have any questions, do not hesitate to contact us on our contact email through the online support chat on our website.

To complete the registration please follow the link below and confirm your e-mail address:
{{config('app.website_url') . '/user/activation/' . md5($user->email)}}
@stop