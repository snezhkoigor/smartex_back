<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ResetPasswordService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
	private $reset_password_service;

	public function __construct(ResetPasswordService $reset_password_service)
	{
		$this->reset_password_service = $reset_password_service;
	}

	public function rules()
	{
		return [
			'email' => 'required|email|exists:users'
		];
	}

	public function messages()
	{
		return [
			'email.required' => 'Enter your e-mail address',
			'email.exists' => 'Can not find this e-mail',
			'email.email' => 'Bad e-mail format'
		];
	}

	public function resetPassword(Request $request)
	{
		$this->validate($request, $this->rules(), $this->messages());

		$user = User::where('email', $request->get('email'))->first();
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		try {
			$user->password = Hash::make($this->reset_password_service->getProcessedResetPassword($user));
			$user->save([], true);
		} catch (\Exception $e) {
			throw new SystemErrorException('Reset password reset failed', $e);
		}

		return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
	}
}