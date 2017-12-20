<?php

namespace App\Services;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PayeerService
{
	public static function getWalletBalance($user, $password, $wallet, $currency)
	{
		$balance = null;
		$response = self::getResponse(['action' => 'balance', 'account' => $wallet, 'apiId' => $user, 'apiPass' => $password]);

		foreach($response['balance'] as $key => $value) {
			if (mb_strtolower($key) === mb_strtolower($currency)) {
				$balance = $value[$currency]['DOSTUPNO'];
			}
		}

		if (empty($balance)) {
			throw new UnprocessableEntityHttpException('No balance found for wallet "' . $wallet . '"');
		}

		return $balance;
	}

	protected static function getResponse($arPost)
	{
		$data = [];
		foreach ($arPost as $k => $v) {
			$data[] = urlencode($k) . '=' . urlencode($v);
		}

		$data[] = 'language=ru';
		$data = implode('&', $data);

		$handler  = curl_init();
		curl_setopt($handler, CURLOPT_URL, 'https://payeer.com/ajax/api/api.php');
		curl_setopt($handler, CURLOPT_HEADER, 0);
		curl_setopt($handler, CURLOPT_POST, true);
		curl_setopt($handler, CURLOPT_POSTFIELDS, $data);
		curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($handler, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0');
		curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);

		$content = curl_exec($handler);

		curl_close($handler);

		$content = json_decode($content, true);

		if (isset($content['errors']) && !empty($content['errors'])) {
			throw new UnprocessableEntityHttpException(implode('; ', $content['errors']));
		}

		return $content;
	}
}