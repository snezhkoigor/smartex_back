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
	 * @param $user
	 * @return string
	 */
	public function getProcessedResetPassword($user): string
	{
		$password = User::generatePassword(6);

		Mail::to($user->email)->send(new ResetPasswordMail($password));

		return $password;
	}
}