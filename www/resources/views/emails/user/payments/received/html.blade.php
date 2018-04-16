@extends('emails.layouts.html.index')

@section('content')
<p>Dear client{{$user->name ? ', ' . $user->name : ''}}!</p>
<p>We received the payment of your order with transaction ID:<b>{{$exchange->id}}</b> (payment ID: {{$payment->id}}).</p>
<p>Your order should be completed in few minutes during the working hours.</p>
@stop