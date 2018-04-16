<?php

namespace App\Services;

use App\Exceptions\SystemErrorException;
use App\Models\Exchange;
use App\Models\Payment;
use App\Models\Wallet;
use Carbon\Carbon;
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
		$wallet = Wallet::query()->where('id', $wallet_id)->first();
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}
		$exchange = Exchange::query()->where('id', $exchange_id)->first();
		if ($exchange === null) {
			throw new NotFoundHttpException('Exchange transaction not found');
		}

		return [
			'auto' => true,
			'id' => $exchange_id,
			'url' => 'https://perfectmoney.is/api/step1.asp',
			'method' => 'POST',
			'params' => [
				[
					'type' => 'hidden',
					'name' => 'PAYEE_NAME',
					'value' => 'Payment ' . $exchange_id,
				],
				[
					'type' => 'hidden',
					'name' => 'PAYMENT_ID',
					'value' => $exchange_id,
				],
				[
					'type' => 'hidden',
					'name' => 'PAYEE_ACCOUNT',
					'value' => $wallet->account,
				],
				[
					'type' => 'hidden',
					'name' => 'PAYMENT_AMOUNT',
					'value' => $amount,
				],
				[
					'type' => 'hidden',
					'name' => 'PAYMENT_UNITS',
					'value' => strtoupper($currency),
				],
				[
					'type' => 'hidden',
					'name' => 'STATUS_URL',
					'value' => config('app.url') . '/sci/payment/' . $wallet->ps_type,
				],
				[
					'type' => 'hidden',
					'name' => 'PAYMENT_URL',
					'value' => config('app.website_url') . '/payment/' . $wallet->ps_type . '/success',
				],
				[
					'type' => 'hidden',
					'name' => 'PAYMENT_URL_METHOD',
					'value' => 'POST',
				],
				[
					'type' => 'hidden',
					'name' => 'NOPAYMENT_URL',
					'value' => config('app.website_url') . '/payment/' . $wallet->ps_type . '/fail',
				],
				[
					'type' => 'hidden',
					'name' => 'NOPAYMENT_URL_METHOD',
					'value' => 'POST',
				],
				[
					'type' => 'hidden',
					'name' => 'SUGGESTED_MEMO',
					'value' => '',
				],
			]
		];
	}
	
	
	/**
	 * @param $data
	 * @param $type
	 * @return Payment
	 * @throws \Exception
	 *
	 * 1 - ввод
	 * 2 - вывод
	 */
	public static function processTransaction($data, $type = 1): Payment
	{
		if (!isset($data['PAYMENT_ID']))
		{
			throw new SystemErrorException('Exchange transaction not set');
		}
		$exchange = Exchange::query()->where('id', $data['PAYMENT_ID'])->first();
		if ($exchange === null)
		{
			throw new NotFoundHttpException('Exchange transaction not found');
		}
		$wallet = Wallet::query()->where('account', $data['PAYEE_ACCOUNT'])->first();
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}

		if ((int)$exchange->in_id_pay > 0)
		{
			throw new SystemErrorException('in_id_pay is already created');
		}
		if ($data['PAYEE_ACCOUNT'] !== $wallet->account)
		{
			throw new SystemErrorException('Wrong PAYEE_ACCOUNT');
		}
		if ($exchange->in_amount !== $data['PAYMENT_AMOUNT'] && $data['PAYMENT_UNITS'] !== strtoupper($exchange->in_currency))
		{
			throw new SystemErrorException('Wrong PAYMENT_AMOUNT and PAYMENT_UNITS');
		}
		
		$string =
		      $data['PAYMENT_ID'] . ':' . $data['PAYEE_ACCOUNT'] . ':' .
		      $data['PAYMENT_AMOUNT'] . ':' . $data['PAYMENT_UNITS'] . ':' .
		      $data['PAYMENT_BATCH_NUM'] . ':' .
		      $data['PAYER_ACCOUNT'] . ':' . strtoupper(md5($wallet->secret)) . ':' .
		      $data['TIMESTAMPGMT'];

		$hash = strtoupper(md5($string));
		if ($hash !== $data['V2_HASH'])
		{
			throw new SystemErrorException('Wrong V2_HASH');
		}

		try
		{
			$payment = new Payment();
			$payment->id_user = $exchange->id_user;
			$payment->id_account = $wallet->id;
			$payment->date = Carbon::today()->format('Y-m-d H:i:s');
			$payment->type = $type;
			$payment->payee = $exchange->in_payee;
			$payment->payer = $exchange->out_payer;
			$payment->id_user_details = null;
			$payment->amount = $exchange->in_amount;
			$payment->currency = $exchange->in_currency;
			$payment->fee = $exchange->in_fee;
			$payment->batch = null;
			$payment->date_confirm = Carbon::today()->format('Y-m-d H:i:s');
			$payment->comment = null;
			$payment->confirm = true;
			$payment->btc_check = null;
			$payment->save();
			
			$exchange->in_id_pay = $payment->id;
			$exchange->save();
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Adding income payment failed');
		}

		return $payment;
	}
}