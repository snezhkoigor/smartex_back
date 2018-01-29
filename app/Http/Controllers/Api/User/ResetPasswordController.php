<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Models\User;
use App\Services\ResetPasswordService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

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
			'email' => 'required|email|exists:users,email'
		];
	}

	public function messages()
	{
		return [
			'email.required' => 'Enter email',
			'email.exists' => 'No email in DB',
			'email.email' => 'Bad email format'
		];
	}


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function resetPassword(Request $request): JsonResponse
	{
		$this->validate($request, $this->rules(), $this->messages());

		$user = User::query()->where('email', $request->get('email'))->first();
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		try {
			$user->password = Hash::make($this->reset_password_service->getProcessedResetPassword($user));
			$user->save();
		} catch (\Exception $e) {
			throw new SystemErrorException('Reset password reset failed', $e);
		}

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}
}
