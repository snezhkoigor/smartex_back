<?php

namespace App\Mail;

use App\Models\Exchange;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExchangeCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $exchange;
    
    /**
     * Create a new message instance.
     *
     * @param $exchange
     * @return void
     */
    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
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
	            'exchange' => $this->exchange
            ])
	        ->subject('Your order was completed on ' . config('app.name') . '.')
	        ->text('emails.user.exchanges.completed.plain');
    }
}
