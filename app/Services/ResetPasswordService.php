<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ResetPasswordService
{
	public function getProcessedResetPassword(User $user)
	{
		$password = User::generatePassword(5);

		Mail::to($user->email)->send(new ResetPasswordMail($password));

		return $password;
	}
}