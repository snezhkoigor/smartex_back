<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailActivationSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
	
	
	/**
	 * EmailActivationSuccessMail constructor.
	 * @param User $user
	 */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->view('emails.user.activation.html')
	        ->with([
	            'user' => $this->user,
            ])
	        ->subject('Reset password on ' . config('app.name') . '.')
	        ->text('emails.user.activation.plain');
    }
}
