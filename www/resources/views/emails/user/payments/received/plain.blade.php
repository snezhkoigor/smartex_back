@extends('emails.layouts.plain.index')

@section('content')
Dear client{{$user->name ? ', ' . $user->name : ''}}!

We received the payment of your order with transaction ID:{{$exchange->id}} (payment ID: {{$payment->id}}).
Your order should be completed in few minutes during the working hours.
@stop