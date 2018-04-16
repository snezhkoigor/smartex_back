<?php

namespace App\Services\Advcash;

use App\Models\Exchange;
use App\Models\Wallet;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AdvcashService
{
	/**
	 * @param $id_payee
	 * @param $user
	 * @param $password
	 * @param $wallet
	 * @return mixed|null
	 *
	 * @throws \Exception
	 *
	 */
	public static function getWalletBalance($id_payee, $user, $password, $wallet)
	{
		$balance = null;
		$merchantWebService = new MerchantWebService();

		$arg0 = new authDTO();
		$arg0->apiName = $id_payee;
		$arg0->accountEmail = $user;
		$arg0->authenticationToken = $merchantWebService->getAuthenticationToken($password);

		$getBalances = new getBalances();
		$getBalances->arg0 = $arg0;

		try {
			$getBalancesResponse = $merchantWebService->getBalances($getBalances);

			foreach($getBalancesResponse->return as $key => $value) {
				$arr = (array)$value;
				if ($arr['id'] === $wallet) {
					$balance = $arr['amount'];
				}
			}
		} catch (\Exception $e) {
			throw new UnprocessableEntityHttpException($e->getMessage());
		}

		if (empty($balance)) {
			throw new UnprocessableEntityHttpException('No balance found for wallet "' . $wallet . '"');
		}

		return $balance;
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

		return [
			'auto' => true,
			'id' => $exchange_id,
			'hash' => $hash,
			'url' => 'https://wallet.advcash.com/sci/',
			'method' => 'POST',
			'params' => [
				[
					'type' => 'hidden',
					'name' => 'ac_account_email',
					'value' => $wallet->account
				],
				[
					'type' => 'hidden',
					'name' => 'ac_sci_name',
					'value' => $wallet->adv_sci
				],
				[
					'type' => 'hidden',
					'name' => 'ac_amount',
					'value' => $amount
				],
				[
					'type' => 'hidden',
					'name' => 'ac_currency',
					'value' => strtoupper($currency)
				],
				[
					'type' => 'hidden',
					'name' => 'ac_comments',
					'value' => 'Payment ' . $exchange_id
				],
				[
					'type' => 'hidden',
					'name' => 'operation_id',
					'value' => $exchange_id
				],
				[
					'type' => 'hidden',
					'name' => 'ac_order_id',
					'value' => $exchange_id
				],
				[
					'type' => 'hidden',
					'name' => 'ac_fail_url',
					'value' => config('app.website_url') . '/payment/' . $wallet->ps_type . '/fail'
				],
				[
					'type' => 'hidden',
					'name' => 'ac_fail_url_method',
					'value' => 'POST'
				]
			]
		];
	}
}