@extends('emails.layouts.plain.index')

@section('content')
Dear client{{$user->name ? ', ' . $user->name : ''}}!
This email announce that your verification documents were successfully uploaded to our system.
Our billing department will process your verification within 3 business hours when online. Verification should not take longer than 48 hours on weekends.
Thank you for patience!
@stop