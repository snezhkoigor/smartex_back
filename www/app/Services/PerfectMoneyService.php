<?php

namespace App\Services;

use App\Exceptions\SystemErrorException;
use App\Mail\ExchangeCompletedMail;
use App\Mail\IncomePaymentSucceedMail;
use App\Models\Exchange;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\PaymentRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
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
	 * @throws \Exception
	 *
	 * @return float|null|UnprocessableEntityHttpException
	 */
	public static function getWalletBalance($user, $password, $wallet)
	{
		$walletObj = Wallet::query()->where('account', $wallet)->first();
		if ($walletObj === null) {
			throw new NotFoundHttpException('Wallet not found');
		}

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

		$walletObj->balance = $balance;
		$walletObj->save();

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
					'value' => number_format($amount, 2, '.', ''),
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
	 * @param $queue_id
	 * @return Payment
	 * @throws \Exception
	 *
	 * 1 - ввод
	 * 2 - вывод
	 */
	public static function processIncomeTransaction($data, $queue_id): Payment
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

		$payment = Payment::query()->where([
			[ 'id', $exchange->in_id_pay ],
			[ 'confirm', '=', 0 ]
		])->first();
		if ($payment === null)
		{
			throw new NotFoundHttpException('Exchange income transaction not found or confirmed');
		}

		$user = User::query()->where('id', $exchange->id_user)->first();
		if ($user === null)
		{
			throw new NotFoundHttpException('Exchange user transaction not found');
		}

		$wallet = Wallet::query()->where('account', $data['PAYEE_ACCOUNT'])->first();
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}

		if ($data['PAYEE_ACCOUNT'] !== $wallet->account)
		{
			throw new SystemErrorException('Wrong PAYEE_ACCOUNT');
		}

		if (number_format($exchange->in_amount, 2, '.', '') !== $data['PAYMENT_AMOUNT'] || strtoupper($data['PAYMENT_UNITS']) !== strtoupper($exchange->in_currency))
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
			PaymentService::confirm($payment);
		}
		catch (\Exception $e)
		{
			\DB::table('payment_answers_queue')
		        ->where('id', $queue_id)
		        ->update(['active' => 0]);

			throw new SystemErrorException('Adding income payment failed');
		}

		return $payment;
	}


	/**
	 * @param $exchange
	 * @return Payment
	 * @throws \Exception
	 *
	 * 1 - ввод
	 * 2 - вывод
	 */
	public static function processOutTransaction(Exchange $exchange): Payment
	{
		/*
			This script demonstrates transfer proccess between two
			PerfectMoney accounts using PerfectMoney API interface.
		*/
		$payment = Payment::query()->where([
			[ 'id', $exchange->out_id_pay ],
			[ 'confirm', '=', 0 ]
		])->first();
		if ($payment === null)
		{
			throw new NotFoundHttpException('Exchange outcome transaction not found or confirmed');
		}

		$wallet = Wallet::query()->where([
			[ 'ps_type', $exchange->out_payment ],
			[ 'currency', $exchange->out_currency ]
		])->first();
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}

		$user = User::query()->where('id', $exchange->id_user)->first();
		if ($user === null)
		{
			throw new NotFoundHttpException('Exchange user transaction not found');
		}

		// trying to open URL to process PerfectMoney Spend request
		$f = file_get_contents('https://perfectmoney.is/acct/confirm.asp?AccountID=' . $wallet->user . '&PassPhrase=' . $wallet->password . '&Payer_Account=' . $wallet->account . '&Payee_Account=' . $exchange->out_payee . '&Amount=' . number_format($exchange->out_amount, 2, '.', '') . '&PAYMENT_ID=' . $exchange->out_id_pay);

		if ($f === false)
		{
		   throw new NotFoundHttpException('error opening url');
		}

		// getting data
		$out = '';
		while (!feof($f))
		{
			$out .= fgets($f);
		}

		fclose($f);

		// searching for hidden fields
		if (!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $out, $result, PREG_SET_ORDER)){
		   throw new NotFoundHttpException('Invalid output');
		}

		return $payment;
	}
}