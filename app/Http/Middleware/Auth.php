<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JWTAuth;

class Auth
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$jwt_user = JWTAuth::toUser(JWTAuth::getToken());

		$user = User::where('id', $jwt_user['id'])
			->with(['roles'])
			->first();

		return $this->response($request, $next, $user);
	}

	/**
	 * @param Request $request
	 * @param Closure $next
	 * @param User $user
	 * @return Response
	 */
	protected function response(Request $request, Closure $next, User $user)
	{
		$response = $next($request);

		$data = json_decode($response->getContent(), true);
		if (!empty($data)) {
			$data['auth_user_data'] = $user;

			$response->setContent(json_encode($data));
		}

		return $response;
	}
}