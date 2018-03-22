<?php

namespace App\Http\Controllers\Api\User;

use App\Models\LoginLog;
use App\Services\LoginService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
	private $loginService;

	public function rules()
	{
		return [
			'email' => 'required|email',
			'password' => 'required'
		];
	}

	public function messages()
	{
		return [
			'email.required' => 'Enter email',
			'email.email' => 'Bad email format',
			'password.required' => 'Enter your current password',
		];
	}

	public function __construct(LoginService $loginService)
	{
		$this->loginService = $loginService;
	}


	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function login(Request $request): JsonResponse
	{
		$this->validate($request, $this->rules(), $this->messages());

		try
		{
			$email = $request->get('email');
			$password = $request->get('password');

			$token = $this->loginService->attemptLogin($email, $password);
		}
		catch(\Exception $e)
		{
			if ($e->getMessage() === 'email')
			{
				return response()->json(['errors' => ['email' => 'Bad email. No user with this e-mail']], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

			return response()->json(['errors' => ['password' => 'Bad password']], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		return response()->json(['data' => $token]);
	}


	/**
	 * @return JsonResponse
	 *
	 * @throws \Exception
	 *
	 */
	public function refresh(): JsonResponse
	{
		return response()->json($this->loginService->attemptRefresh());
	}

	public function logout(Request $request)
	{
		$loginLog = LoginLog::query()->where('token', $request->bearerToken())->first();
		if ($loginLog)
		{
			$loginLog->token_id = null;
			$loginLog->token = null;
			$loginLog->save();
		}
		
		$this->loginService->logout();

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}
}