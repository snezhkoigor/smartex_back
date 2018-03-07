<?php

namespace App\Transformers;

use App\Models\Commission;
use App\Repositories\CurrencyRepository;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Item;

class CommissionTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'paymentSystem',
		'wallet'
	];


	/**
	 * @param Commission $commission
	 * @return array
	 */
	public function transform(Commission $commission): array
	{
		$data = [
			'id' => (int)$commission->id,
			'wallet_id' => (int)$commission->wallet_id,
			'payment_system_id' => (int)$commission->payment_system_id,
			'currency' => $commission->currency,
			'prefix' => CurrencyRepository::getAvailableCurrencies()[$commission->currency]['prefix'],
			'commission' => (float)$commission->commission,
			'active' => (bool)$commission->active
		];

		return $data;
	}


	/**
	 * @param Commission $commission
	 * @return \League\Fractal\Resource\Item
	 */
	public function includePaymentSystem(Commission $commission): Item
	{
		return $this->item($commission->paymentSystem, new PaymentSystemTransformer(), 'paymentSystem');
	}


	/**
	 * @param Commission $commission
	 * @return \League\Fractal\Resource\Item
	 */
	public function includeWallet(Commission $commission): Item
	{
		return $this->item($commission->wallet, new WalletTransformer(), 'wallet');
	}
}