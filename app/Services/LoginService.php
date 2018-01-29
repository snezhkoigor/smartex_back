<?php

namespace App\Services;

use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class LoginService
{
	const REFRESH_TOKEN = 'refreshToken';

	private $apiConsumer;
	private $cookie;
	private $db;
	private $request;

	public function __construct(Application $app) {
		// для организпции http запросов внутри проекта
		$this->apiConsumer = $app->make('apiconsumer');
		$this->cookie = $app->make('cookie');
		$this->db = $app->make('db');
		$this->request = $app->make('request');
	}


	/**
	 * @param $email
	 * @param $password
	 * @return array|\Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 *
	 */
	public function attemptLogin($email, $password)
	{
		$user = User::query()->where('email', $email)->first();

		if ($user) {
			return $this->proxy('password', [
				'username' => $email,
				'password' => $password
			]);
		}

		throw new InvalidCredentialsException('email');
	}

	/**
	 * Attempt to refresh the access token used a refresh token that
	 * has been saved in a cookie
	 *
	 * @throws \Exception
	 *
	 */
	public function attemptRefresh(): array
	{
		$refreshToken = $this->request::cookie(self::REFRESH_TOKEN);

		return $this->proxy('refresh_token', [
			'refresh_token' => $refreshToken
		]);
	}


	/**
	 * @param $grantType
	 * @param array $data
	 * @return array
	 *
	 * @throws \Exception
	 *
	 */
	public function proxy($grantType, array $data = []): array
	{
		$data = array_merge($data, [
			'client_id' => env('PASSWORD_CLIENT_ID'),
			'client_secret' => env('PASSWORD_CLIENT_SECRET'),
			'grant_type' => $grantType
		]);

		$response = $this->apiConsumer->post('/oauth/token', $data);

		if (!$response->isSuccessful()) {
			throw new InvalidCredentialsException('other');
		}

		$data = json_decode($response->getContent());

		// Create a refresh token cookie
		$this->cookie->queue(
			self::REFRESH_TOKEN,
			$data->refresh_token,
			864000, // 10 days
			null,
			null,
			false,
			true // HttpOnly
		);

		return [
			'access_token' => $data->access_token,
			'expires_in' => $data->expires_in
		];
	}


	/**
	 *
	 */
	public function logout()
	{
		$user = Auth::guard()->user();

		if ($user)
		{
			$accessToken = $user->token();

			if ($accessToken)
			{
				$this->db
					->table('oauth_refresh_tokens')
					->where('access_token_id', $accessToken->id)
					->update([
						'revoked' => true
					]);

				$accessToken->revoke();

				$this->cookie->queue($this->cookie->forget(self::REFRESH_TOKEN));
			}
		}
	}
}