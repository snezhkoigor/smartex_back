@extends('emails.layouts.plain.index')

@section('content')
Dear client{{$user->name ? ', ' . $user->name : ''}}!

We are happy to announce that your verification is completed.
Now you can make transactions over 1000€ or equivalent in other currency.
If you have any additional questions, feel free to contact our support team.
Thank you for your patience.
@stop