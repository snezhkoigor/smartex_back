<?php

namespace App\Transformers;

use App\Models\PaymentSystem;
use League\Fractal\TransformerAbstract;

class PaymentSystemTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'wallets'
	];

	public function transform(PaymentSystem $payment_system)
	{
		$data = [
			'id' => (int)$payment_system->id,
			'name' => $payment_system->name,
			'logo' => $payment_system->logo,
			'active' => (bool)$payment_system->active,
			'is_account_multi_line' => (bool)$payment_system->is_account_multi_line,
			'created_at' => $payment_system->created_at,
			'updated_at' => $payment_system->updated_at
		];

		return $data;
	}

	public function includeWallets(PaymentSystem $payment_system)
	{
		$wallets = $payment_system->wallets()
			->where('is_deleted', 0)->get();

		return $this->collection($wallets, new WalletTransformer(), 'wallets');
	}
}