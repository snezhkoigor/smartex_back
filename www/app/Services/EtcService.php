<?php

namespace App\Services;

use App\Models\Exchange;
use App\Models\Wallet;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class EtcService
 * @package App\Services
 */
class EtcService
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


	/**
	 * @param $wallet_id
	 * @param $amount
	 * @param $currency
	 * @param $exchange_id
	 * @return array
	 *
	 * @throws \Exception
	 */
	public static function getForm($wallet_id, $amount, $currency, $exchange_id): array
	{
		$wallet = Wallet::query()->where('id', $wallet_id)->first();
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}
		$exchange = Exchange::query()->where('id', $exchange_id)->first();
		if ($exchange === null) {
			throw new NotFoundHttpException('Exchange transaction not found');
		}

		$hash = md5(time());
		Redis::set($hash, $exchange_id, 'EX', Exchange::$redis_hash_expiration);

        $amount = number_format($amount, 2, '.', '');
        $currency = strtoupper($currency);
        $description = base64_encode('Payment ' . $exchange_id);
		return [
			'auto' => false,
			'id' => $exchange_id,
			'hash' => $hash,
			'url' => null,
			'method' => null,
			'params' => [
				[
					'type' => 'text',
					'name' => 'wallet',
					'value' => $wallet->account,
				],
				[
					'type' => 'text',
					'name' => 'amount',
					'value' => $amount,
				],
				[
					'type' => 'text',
					'name' => 'currency',
					'value' => $currency,
				],
				[
					'type' => 'text',
					'name' => 'description',
					'value' => $description,
				]
			]
		];
	}
}