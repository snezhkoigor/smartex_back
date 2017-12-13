<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordChanged;
use App\Mail\ResetPasswordRequest;
use App\Models\User;
use App\Services\ResetPasswordService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
	protected $resetPasswordService;

	public function rules()
	{
		return [
			'email' => 'required|exists:users|email'
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

	public function __construct(ResetPasswordService $resetPasswordService)
	{
		$this->resetPasswordService = $resetPasswordService;
	}

	public function request(Request $request)
	{
		$this->validate($request, $this->rules(), $this->messages());
		$user = User::where('email', $request->get('email'))->first();

		if (null === $user) {
			throw new NotFoundHttpException('User not found');
		}

		$token = $this->resetPasswordService->token($user);

		if (null === $token) {
			return response()->json([ 'message' => 'We has already sent you email for continue' ], Response::HTTP_BAD_REQUEST);
		}

		Mail::to($user->email)
			->send(new ResetPasswordRequest($token));

		return response()->json([ 'message' => 'Check your email for continue' ], Response::HTTP_OK);
	}

	public function change($token)
	{
		$data = $this->resetPasswordService->getByToken($token);
		if (!$data) {
			throw new NotFoundHttpException('Bad activation token');
		}

		$user = User::findOrFial($data->user_id);
		$password = User::generate_password(5);
		$user->password = Hash::make($password);
		$user->save();

		Mail::to($user->email)
			->send(new ResetPasswordChanged($password));

		$this->resetPasswordService->delete($token);

		return response()->json([ 'message' => 'Check your email to see new password' ], Response::HTTP_OK);
	}
}