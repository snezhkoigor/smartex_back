<?php

namespace App\Services;

use App\Models\Exchange;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Support\Facades\Redis;
use Intervention\Image\Exception\NotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class PayeerService
 * @package App\Services
 */
class PayeerService
{
	/**
	 * @param $user
	 * @param $password
	 * @param $wallet
	 * @param $currency
	 *
	 * @throws \Exception
	 *
	 * @return null|float
	 *
	 */
	public static function getWalletBalance($user, $password, $wallet, $currency)
	{
		$balance = null;
		$response = self::getResponse(['action' => 'balance', 'account' => $wallet, 'apiId' => $user, 'apiPass' => $password]);

		$walletObj = Wallet::query()->where('account', $wallet)->first();
		if ($walletObj === null) {
			throw new NotFoundHttpException('Wallet not found');
		}

		foreach($response['balance'] as $key => $value) {
			if (mb_strtolower($key) === mb_strtolower($currency)) {
				$balance = $value[$currency]['DOSTUPNO'];
			}
		}

		if (empty($balance)) {
			throw new UnprocessableEntityHttpException('No balance found for wallet "' . $wallet . '"');
		}

		$walletObj->balance = $balance;
		$walletObj->save();

		return $balance;
	}


	/**
	 * @param $arPost
	 *
	 * @throws UnprocessableEntityHttpException
	 *
	 * @return mixed
	 *
	 */
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
			'auto' => true,
			'id' => $exchange_id,
			'hash' => $hash,
			'url' => '//payeer.com/api/merchant/m.php',
			'method' => 'GET',
			'params' => [
				[
					'type' => 'hidden',
					'name' => 'm_shop',
					'value' => $wallet->account,
				],
				[
					'type' => 'hidden',
					'name' => 'm_orderid',
					'value' => $exchange_id,
				],
				[
					'type' => 'hidden',
					'name' => 'm_amount',
					'value' => $amount,
				],
				[
					'type' => 'hidden',
					'name' => 'm_curr',
					'value' => $currency,
				],
				[
					'type' => 'hidden',
					'name' => 'm_desc',
					'value' => $description,
				],
				[
					'type' => 'hidden',
					'name' => 'm_sign',
					'value' => strtoupper(hash('sha256', implode(':', [
						$wallet->account,
					    $exchange_id,
					    $amount,
					    $currency,
					    $description,
					    $wallet->secret
					]))),
				],
				[
					'type' => 'hidden',
					'name' => 'lang',
					'value' => 'en',
				]
			]
		];
	}


	/**
	 * @param $data
	 * @return Payment
	 * @throws \Exception
	 *
	 * 1 - ввод
	 * 2 - вывод
	 */
	public static function processIncomeTransaction($data): Payment
	{
	
	}
}