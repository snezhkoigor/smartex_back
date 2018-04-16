@extends('emails.layouts.plain.index')

@section('content')
Dear client{{$user->name ? ', ' . $user->name : ''}}!

We got the message about the placed order and expect your payment.
Transaction ID: {{$exchange->id}}.
Exchange direction: {{$exchange->in_currency}} {{$exchange->in_amount}} --> {{$exchange->out_currency}} {{$exchange->out_amount}}.
If you want to ask about something regarding your exchange, please contact us by mail or thru our website online chat. As a subject use the transaction ID.
@stop