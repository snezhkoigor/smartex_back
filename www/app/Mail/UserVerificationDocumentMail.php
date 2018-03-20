<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserVerificationDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $type;
	
	
	/**
	 * EmailActivationSuccessMail constructor.
	 * @param User $user
	 * @param string $type
	 */
    public function __construct(User $user, $type = 'start')
    {
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->view('emails.user.documents.verification.'.$this->type.'.html')
	        ->with([
	            'user' => $this->user,
            ])
	        ->subject(($this->type === 'start' ? 'Documents successfully uploaded' : 'Your verification is completed') . ' on ' . config('app.name') . '.')
	        ->text('emails.user.documents.verification.'.$this->type.'.plain');
    }
}
