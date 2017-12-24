<?php

namespace App\Transformers;

use App\Models\Commission;
use App\Repositories\PaymentSystemRepository;
use League\Fractal\TransformerAbstract;

class CommissionTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'paymentSystem',
		'wallet'
	];

	public function transform(Commission $commission)
	{
		$data = [
			'id' => (int)$commission->id,
			'wallet_id' => (int)$commission->wallet_id,
			'payment_system_id' => (int)$commission->payment_system_id,
			'currency' => $commission->currency,
			'prefix' => PaymentSystemRepository::getAvailableCurrencies()[$commission->currency]['prefix'],
			'commission' => (float)$commission->commission,
			'active' => (bool)$commission->active,
			'is_deleted' => (bool)$commission->is_deleted,
		];

		return $data;
	}

	public function includePaymentSystem(Commission $commission)
	{
		return $this->item($commission->paymentSystem, new PaymentSystemTransformer(), 'paymentSystem');
	}

	public function includeWallet(Commission $commission)
	{
		return $this->item($commission->wallet, new WalletTransformer(), 'wallet');
	}
}