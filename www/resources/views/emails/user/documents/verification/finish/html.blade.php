@extends('emails.layouts.html.index')

@section('content')
<p>Dear client{{$user->name ? ', ' . $user->name : ''}}!</p>
<p>We are happy to announce that your verification is completed.</p>
<p>Now you can make transactions over 1000€ or equivalent in other currency.</p>
<p>If you have any additional questions, feel free to contact our support team.</p>
<p>Thank you for your patience.</p>
@stop