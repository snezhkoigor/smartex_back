<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    private $password;


	/**
	 * ResetPasswordMail constructor.
	 * @param $password
	 */
    public function __construct($password)
    {
        $this->password = $password;
    }


	/**
	 * @return $this
	 */
    public function build(): ResetPasswordMail
    {
        return $this->view('emails.user.password.html.reset')
	        ->with([
	            'password' => $this->password,
            ])
	        ->subject('Password has been changed on ' . config('app.name') . '.')
	        ->text('emails.user.password.plain.reset');
    }
}
