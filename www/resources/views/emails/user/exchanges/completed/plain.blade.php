@extends('emails.layouts.plain.index')

@section('content')
Dear client{{$user->name ? ', ' . $user->name : ''}}!

The order with transaction ID: {{$exchange->id}} was successfully completed!
Thank you for choosing our services.
@stop