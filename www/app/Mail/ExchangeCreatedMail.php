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
    private $hash;
    
    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $exchange
     * @return void
     */
    public function __construct(User $user, Exchange $exchange, string $hash = null)
    {
        $this->user = $user;
        $this->exchange = $exchange;
        $this->hash = $hash;
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
	            'user' => $this->user,
	            'hash' => $this->hash
            ])
	        ->subject('Order accepted on ' . config('app.name') . '.')
	        ->text('emails.user.exchanges.created.plain');
    }
}
