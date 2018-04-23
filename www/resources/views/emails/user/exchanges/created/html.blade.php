@extends('emails.layouts.html.index')

@section('content')
<p>Dear client{{$user->name ? ', ' . $user->name : ''}}!</p>
<p>We got the message about the placed order and expect your payment.</p>
<p>Transaction ID: <b>@if ($hash)<a href="{{config('app.website_url')}}/payment/{{$hash}}" target="_blank">@endif{{$exchange->id}}@if ($hash)</a>@endif</b>.</p>
<p>Exchange direction: <b>{{$exchange->in_currency}} {{$exchange->in_amount}} --> {{$exchange->out_currency}} {{$exchange->out_amount}}</b>.</p>
<p>If you want to ask about something regarding your exchange, please contact us by mail or through our website online chat. As a subject use the transaction ID.</p>
@stop