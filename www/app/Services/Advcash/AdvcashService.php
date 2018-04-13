<?php

namespace App\Services\Advcash;

use App\Models\Exchange;
use App\Models\Wallet;
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
		$wallet = Wallet::find($wallet_id);
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}
		$exchange = Exchange::find($exchange_id);
		if ($exchange === null) {
			throw new NotFoundHttpException('Exchange transaction not found');
		}

		return [
			'auto' => true,
			'url' => 'https://wallet.advcash.com/sci/',
			'method' => 'POST',
			'params' => [
				'ac_account_email' => $wallet->account,
				'ac_sci_name' => $wallet->adv_sci,
				'ac_amount' => $amount,
				'ac_currency' => $currency,
				'ac_comments' => 'Payment ' . $exchange_id,
				'operation_id' => $exchange_id,
				'ac_order_id' => $exchange_id,
				'ac_fail_url' => config('app.website_url') . '/payment/' . $wallet->ps_type . '/fail',
				'ac_fail_url_method' => 'POST',
			]
		];
	}
}