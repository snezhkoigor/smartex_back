<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Response;
use JWTAuth;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

class JwtAuthenticateController extends Controller
{
	public function authenticate(Request $request)
	{
//		throw new NotFoundHttpException('User not found');

		$credentials = $request->only('email', 'password');

		try {
			if (!$token = JWTAuth::attempt($credentials)) {
				$user = User::where('email', '=', $request->get('email'))->first();
				if (!$user) {
					$message['email'] = 'No user with this email';
				} else {
					$message['password'] = 'Bad password';
				}

				return response()->json($message, Response::HTTP_UNPROCESSABLE_ENTITY);
			}
		} catch (JWTException $e) {
			return response()->json(['message' => 'Could not create token'], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		$user = User::where('email', '=', $request->get('email'))
			->with(['roles'])
			->first();

		if (!$user->hasRole([ Role::ROLE_ADMIN, Role::ROLE_OPERATOR ])) {
			throw new BadRequestHttpException('Bad permissions');
		}

		if (null === $user) {
			throw new NotFoundHttpException('User not found');
		}

		return response()->json(['data' => compact('token'), 'auth_user_data' => $user ], Response::HTTP_OK);
	}

	public function logout()
	{
		JWTAuth::invalidate(JWTAuth::getToken());

		return response()->json(['data' => null], Response::HTTP_OK);
	}
}