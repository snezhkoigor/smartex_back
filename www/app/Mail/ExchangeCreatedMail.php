<?php

namespace App\Mail;

use App\Models\Exchange;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExchangeCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $exchange;
    
    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $exchange
     * @return void
     */
    public function __construct(User $user, Exchange $exchange)
    {
        $this->user = $user;
        $this->exchange = $exchange;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->view('emails.user.exchanges.created.html')
	        ->with([
	            'exchange' => $this->exchange,
	            'user' => $this->user
            ])
	        ->subject('Order accepted on ' . config('app.name') . '.')
	        ->text('emails.user.exchanges.created.plain');
    }
}
