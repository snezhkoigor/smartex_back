<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegistrationSuccessMail extends Mailable
{
    use Queueable, SerializesModels;
	
	private $user;
	private $password;

	/**
	 * RegistrationSuccessMail constructor.
	 * @param User $user
	 * @param string $password
	 */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->view('emails.user.registration.html')
	        ->with([
	            'password' => $this->password,
	            'user' => $this->user
            ])
	        ->subject('Confirmation of registration on ' . config('app.name') . '.')
	        ->text('emails.user.registration.plain');
    }
}
