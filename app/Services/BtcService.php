<?php

namespace App\Services;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class BtcService
 * @package App\Services
 */
class BtcService
{
	/**
	 * @param $user
	 * @param null $wallet
	 * @return float
	 *
	 * @throws \Exception
	 *
	 */
	public static function getWalletBalance($user, $wallet = null): float
	{
		$balance = null;

		$handler  = curl_init();
		curl_setopt($handler, CURLOPT_URL, 'https://block.io/api/v2/get_balance/?api_key=' . $user);
		curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);

		$content = curl_exec($handler);
		curl_close($handler);

		$answer = json_decode($content, true);
		if ($answer['status'] === 'fail') {
			throw new UnprocessableEntityHttpException($answer['data']['error_message']);
		}

		return (float)$answer['data']['available_balance'];
	}
}