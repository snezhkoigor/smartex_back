<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordChanged extends Mailable
{
    use Queueable, SerializesModels;

    protected $password;

    /**
     * Create a new message instance.
     *
     * @param string $password
     *
     * @return void
     */
    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
	    return $this->view('emails.user.password.changed')->with([
		    'password' => $this->password,
	    ])->subject(config('frontend.name') . ' :: ' . 'New password.');
    }
}
