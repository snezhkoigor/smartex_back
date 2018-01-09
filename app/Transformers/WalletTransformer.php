<?php

namespace App\Transformers;

use App\Models\Wallet;
use App\Repositories\CurrencyRepository;
use League\Fractal\TransformerAbstract;

/**
 * Class WalletTransformer
 * @package App\Transformers
 */
class WalletTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'commissions',
		'paymentSystem'
	];


	/**
	 * @param Wallet $wallet
	 * @return array
	 */
	public function transform(Wallet $wallet)
	{
		$data = [
			'id' => (int)$wallet->id,
			'ps_type' => $wallet->ps_type,
			'payment_system_id' => (int)$wallet->payment_system_id,
			'currency' => $wallet->currency,
			'prefix' => CurrencyRepository::getAvailableCurrencies()[$wallet->currency]['prefix'],
			'user' => $wallet->user,
			'password' => $wallet->password,
			'secret' => $wallet->secret,
			'adv_sci' => $wallet->adv_sci,
			'id_payee' => $wallet->id_payee,
			'account' => $wallet->account,
			'balance' => (float)$wallet->balance,
			'active' => (bool)$wallet->active
		];

		return $data;
	}


	/**
	 * @param Wallet $wallet
	 * @return \League\Fractal\Resource\Collection
	 */
	public function includeCommissions(Wallet $wallet)
	{
		return $this->collection($wallet->commissions, new CommissionTransformer(), 'commissions');
	}


	/**
	 * @param Wallet $wallet
	 * @return \League\Fractal\Resource\Item
	 */
	public function includePaymentSystem(Wallet $wallet)
	{
		return $this->item($wallet->paymentSystem, new PaymentSystemTransformer(), 'paymentSystem');
	}
}