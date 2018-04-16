<?php

namespace App\Services;

use App\Models\Exchange;
use App\Models\Wallet;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class PerfectMoneyService
 * @package App\Services
 */
class PerfectMoneyService
{
	/**
	 * @param $user
	 * @param $password
	 * @param $wallet
	 *
	 * @throws UnprocessableEntityHttpException
	 *
	 * @return float|null|UnprocessableEntityHttpException
	 */
	public static function getWalletBalance($user, $password, $wallet)
	{
		$balance = null;

		$f = fopen('https://perfectmoney.is/acct/balance.asp?AccountID=' . $user . '&PassPhrase=' . $password, 'rb');

		if ($f === false) {
			throw new UnprocessableEntityHttpException('Error opening url');
		}

		$page_content = '';
		while (!feof($f)) {
			$page_content .= fgets($f);
		}

		fclose($f);

		if (preg_match_all("/<input name='ERROR' type='hidden' value='(.*)'>/", $page_content, $result, PREG_SET_ORDER)) {
			throw new UnprocessableEntityHttpException($result[0][1]);
		}
		if (!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $page_content, $result, PREG_SET_ORDER)) {
			throw new UnprocessableEntityHttpException('Invalid output from payment system');
		}

		foreach($result as $item) {
			if (mb_strtolower($wallet) === mb_strtolower($item[1])) {
				$balance = (float)$item[2];
			}
		}

		if ($balance === null) {
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
			'url' => 'https://perfectmoney.is/api/step1.asp',
			'method' => 'POST',
			'params' => [
				[
					'name' => 'PAYEE_NAME',
					'value' => 'Payment ' . $exchange_id,
				],
				[
					'name' => 'PAYMENT_ID',
					'value' => $exchange_id,
				],
				[
					'name' => 'PAYEE_ACCOUNT',
					'value' => $wallet->account,
				],
				[
					'name' => 'PAYMENT_AMOUNT',
					'value' => $amount,
				],
				[
					'name' => 'PAYMENT_UNITS',
					'value' => strtoupper($currency),
				],
				[
					'name' => 'STATUS_URL',
					'value' => config('app.website_url') . '/api/sci/payment/' . $wallet->ps_type,
				],
				[
					'name' => 'PAYMENT_URL',
					'value' => config('app.website_url') . '/payment/' . $wallet->ps_type . '/success',
				],
				[
					'name' => 'PAYMENT_URL_METHOD',
					'value' => 'POST',
				],
				[
					'name' => 'NOPAYMENT_URL',
					'value' => config('app.website_url') . '/payment/' . $wallet->ps_type . '/fail',
				],
				[
					'name' => 'NOPAYMENT_URL_METHOD',
					'value' => 'POST',
				],
				[
					'name' => 'SUGGESTED_MEMO',
					'value' => '',
				],
			]
		];
	}
}