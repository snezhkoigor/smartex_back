<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Models\User;
use App\Services\ResetPasswordService;
use App\Services\UserService;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Hash;
use JWTAuth;

class UserController extends Controller
{
	private $reset_password_service;
	private $user_service;

	public function __construct(UserService $user_service)
	{
		$this->user_service = $user_service;
	}

	public function rules(Request $request, User $user)
	{
		return [
			'email' => 'required|email|' . $this->emailRulesByChanging($request, $user),
			'current_password' => 'required_with:new_password|' . $this->checkCurrentPassword($request, $user)
		];
	}

	public function checkCurrentPassword(Request $request, User $user)
	{
		if ($request->get('new_password') && !Hash::check($request->get('current_password'), $user->password)) {
			return 'same:password';
		}
	}

	public function emailRulesByChanging(Request $request, User $user)
	{
		$email = $request->get('email');
		if ($user && $email !== $user->email) {
			return User::where('email', '=', $email)->exists() ? 'unique:users' : '';
		}
	}

	public function messages()
	{
		return [
			'email.required' => 'Enter email',
			'email.exists' => 'This email use someone else',
			'email.email' => 'Bad email format',
			'current_password.required_with' => 'Enter your current password for changing in new',
			'current_password.same' => 'Wrong password value'
		];
	}


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function resetPassword(Request $request)
	{
		$this->validate($request, $this->rules($request), $this->messages());

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


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function profile(Request $request)
	{
		$fieldsets = $this->getFieldsets($request);
		$includes = $this->getIncludes($request);

		$user = JWTAuth::toUser(JWTAuth::getToken());

		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		return fractal($user, new UserTransformer())
			->parseIncludes($includes)
			->parseFieldsets($fieldsets)
			->respond();
	}


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function updateProfile(Request $request)
	{
		$user = JWTAuth::toUser(JWTAuth::getToken());
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		$this->validate($request, $this->rules($request, $user), $this->messages());

		try {
			$user->fill($request->all());

			if ($request->get('new_password')) {
				$user->password = $request->get('new_password') ? Hash::make($request->get('new_password')) : $user->password;
			}
			if ($request->get('logo_64_base')) {
				$user->avatar = $this->user_service->getProcessedUserAvatar($request->get('logo_64_base'));
			}

			$user->save([], true);
		} catch (\Exception $e) {
			throw new SystemErrorException('Update user profile failed', $e);
		}

		return fractal($user, new UserTransformer())
			->parseIncludes('roles')
			->respond();
	}
}
