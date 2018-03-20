<?php

namespace App\Services;

use App\Exceptions\InvalidCredentialsException;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Geo;

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
			$answer = $this->proxy('password', [
				'username' => $email,
				'password' => $password
			]);

			$loginLog = new LoginLog();
			$loginLog->user_id = $user->id;
			$loginLog->tech_browser_info = $_SERVER['HTTP_USER_AGENT'];
			
			preg_match('/(MSIE|Opera|Firefox|Chrome|Version)(?:\/| )([0-9.]+)/', $loginLog->tech_browser_info, $bInfo);
			$loginLog->browser = ($bInfo[1] === 'Version') ? 'Safari' : $bInfo[1];

			$loginLog->ip = $_SERVER['REMOTE_ADDR'];

			$geoObj = Geo::get($loginLog->ip);
			if ($geoObj)
			{
				$loginLog->geo = $geoObj->city->name_en;
			}
			
			$loginLog->save();

			return $answer;
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
			'client_id' => 2,
			'client_secret' => config('app.password_secret'),
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
