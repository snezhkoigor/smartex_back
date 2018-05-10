<?php

namespace App\Services\Advcash;

use App\Exceptions\SystemErrorException;
use App\Models\Exchange;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PaymentService;
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

		$walletObj = Wallet::query()->where('account', $wallet)->first();
		if ($walletObj === null) {
			throw new NotFoundHttpException('Wallet not found');
		}

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
		if (!isset($data['ac_order_id']))
		{
			throw new SystemErrorException('Exchange transaction not set');
		}

		$exchange = Exchange::query()->where('id', $data['ac_order_id'])->first();
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

		$wallet = Wallet::query()->where('account', $data['ac_dest_wallet'])->first();
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}

		if ($data['ac_dest_wallet'] !== $wallet->account)
		{
			throw new SystemErrorException('Wrong ac_dest_wallet');
		}

		if ($data['ac_sci_name'] !== $wallet->adv_sci || $exchange->in_amount !== $data['ac_amount'] || $data['ac_merchant_currency'] !== strtoupper($exchange->in_currency))
		{
			throw new SystemErrorException('Wrong ac_amount and ac_merchant_currency ac_sci_name');
		}

		$string = $data['ac_transfer'].':'.$data['ac_start_date'].':'.$data['ac_sci_name'].':'.$data['ac_src_wallet'].':'.$data['ac_dest_wallet'].':'.$data['ac_order_id'].':'.$data['ac_amount'].':'.$data['ac_merchant_currency'].':'.$wallet->secret;
		$hash = hash('sha256', $string);
		if ($hash !== $data['ac_hash'])
		{
			throw new SystemErrorException('Wrong ac_hash');
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

		try {
			$merchantWebService = new MerchantWebService();

			$arg0 = new authDTO();
			$arg0->apiName = $wallet->id_payee;
			$arg0->accountEmail = $wallet->user;
			$arg0->authenticationToken = $merchantWebService->getAuthenticationToken($wallet->password);

			$arg1 = new sendMoneyRequest();
			$arg1->amount = $exchange->out_amount;
			$arg1->currency = strtoupper($exchange->out_currency);
			$arg1->email = $exchange->out_payee;
			//$arg1->walletId = "U000000000000";
			$arg1->note = 'Transaction ' . $exchange->out_id_pay;
			$arg1->savePaymentTemplate = false;

			$validationSendMoney = new validationSendMoney();
			$validationSendMoney->arg0 = $arg0;
			$validationSendMoney->arg1 = $arg1;

			$sendMoney = new sendMoney();
			$sendMoney->arg0 = $arg0;
			$sendMoney->arg1 = $arg1;

		    $merchantWebService->validationSendMoney($validationSendMoney);
		    $merchantWebService->sendMoney($sendMoney);
		} catch (\Exception $e) {
			throw new NotFoundHttpException('Can not withdrawal by adv.');
		}

		return $payment;
	}
}