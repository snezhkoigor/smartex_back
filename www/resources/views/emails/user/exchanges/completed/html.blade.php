@extends('emails.layouts.html.index')

@section('content')
<p>Dear client{{$user->name ? ', ' . $user->name : ''}}!</p>
<p>The order with transaction ID: <b>{{$exchange->id}}</b> was successfully completed!</p>
<p>Thank you for choosing our services.</p>
@stop