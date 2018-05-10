<?php

namespace App\Mail;

use App\Models\Exchange;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class IncomePaymentSucceedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $payment;
    private $exchange;
    
    /**
     * Create a new message instance.
     *
     * @param $payment
     * @param $exchange
     * @return void
     */
    public function __construct(User $user, Payment $payment, Exchange $exchange)
    {
    	$this->user = $user;
        $this->payment = $payment;
        $this->exchange = $exchange;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->view('emails.user.payments.received.html')
	        ->with([
	        	'user' => $this->user,
	            'payment' => $this->payment,
	            'exchange' => $this->exchange
            ])
	        ->subject('Payment received on ' . config('app.name') . '.')
	        ->text('emails.user.payments.received.plain');
    }
}
