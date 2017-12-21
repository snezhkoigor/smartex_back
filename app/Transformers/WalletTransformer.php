<?php

namespace App\Transformers;

use App\Models\Wallet;
use App\Repositories\PaymentSystemRepository;
use Illuminate\Support\Facades\Crypt;
use League\Fractal\TransformerAbstract;

class WalletTransformer extends TransformerAbstract
{
	protected $availableIncludes = [];

	public function transform(Wallet $wallet)
	{
		$data = [
			'id' => (int)$wallet->id,
			'ps_type' => $wallet->ps_type,
			'payment_system_id' => (int)$wallet->payment_system_id,
			'currency' => $wallet->currency,
			'prefix' => PaymentSystemRepository::getAvailableCurrencies()[$wallet->currency]['prefix'],
			'user' => $wallet->user,
			'password' => $wallet->password,
			'secret' => $wallet->secret,
			'adv_sci' => $wallet->adv_sci,
			'id_payee' => $wallet->id_payee,
			'account' => $wallet->account,
			'balance' => (float)$wallet->balance,
			'active' => (bool)$wallet->active,
			'is_deleted' => (bool)$wallet->is_deleted,
		];

		return $data;
	}
}