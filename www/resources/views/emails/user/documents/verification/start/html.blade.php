@extends('emails.layouts.html.index')

@section('content')
<p>Dear client{{$user->name ? ', ' . $user->name : ''}}!</p>
<p>This email announce that your verification documents were successfully uploaded to our system.</p>
<p>Our billing department will process your verification within 3 business hours when online. Verification should not take longer than 48 hours on weekends.</p>
<p>Thank you for patience!</p>
@stop