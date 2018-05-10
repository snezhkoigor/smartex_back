<?php

namespace App\Mail;

use App\Models\Exchange;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExchangeCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $exchange;
    private $user;
    
    /**
     * Create a new message instance.
     *
     * @param $exchange
     * @param $user
     * @return void
     */
    public function __construct(Exchange $exchange, User $user)
    {
        $this->exchange = $exchange;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.user.exchanges.completed.html')
	        ->with([
	            'exchange' => $this->exchange,
	            'user' => $this->user
            ])
	        ->subject('Your order was completed on ' . config('app.name') . '.')
	        ->text('emails.user.exchanges.completed.plain');
    }
}
