<?php

namespace App\Transformers;

use App\Models\PaymentSystem;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;

/**
 * Class PaymentSystemTransformer
 * @package App\Transformers
 */
class PaymentSystemTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'wallets'
	];


	/**
	 * @param PaymentSystem $payment_system
	 * @return array
	 */
	public function transform(PaymentSystem $payment_system): array
	{
		$data = [
			'id' => (int)$payment_system->id,
			'name' => $payment_system->name,
			'logo' => $payment_system->logo,
			'logo_link' => $payment_system->logo ? Storage::disk('logo')->url($payment_system->logo) : '',
			'active' => (bool)$payment_system->active,
			'is_account_multi_line' => (bool)$payment_system->is_account_multi_line,
			'created_at' => $payment_system->created_at,
			'updated_at' => $payment_system->updated_at
		];

		return $data;
	}


	/**
	 * @param PaymentSystem $payment_system
	 * @return \League\Fractal\Resource\Collection
	 */
	public function includeWallets(PaymentSystem $payment_system): Collection
	{
		return $this->collection($payment_system->wallets, new WalletTransformer(), 'wallets');
	}
}