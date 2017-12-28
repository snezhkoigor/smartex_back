<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Class ResetPasswordService
 * @package App\Services
 */
class ResetPasswordService
{
	/**
	 * @param User $user
	 * @return string
	 */
	public function getProcessedResetPassword(User $user)
	{
		$password = User::generatePassword(5);

		Mail::to($user->email)->send(new ResetPasswordMail($password));

		return $password;
	}
}