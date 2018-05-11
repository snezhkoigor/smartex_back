<?php

namespace App\Transformers;

use App\Models\Payment;
use App\Models\User;
use App\Repositories\CurrencyRepository;
use App\Services\Advcash\validateAccount;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Item;

/**
 * Class PaymentTransformer
 * @package App\Transformers
 */
class PaymentTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'user',
		'paymentSystem'
	];


	/**
	 * @param Payment $payment
	 * @return array
	 */
	public function transform(Payment $payment): array
	{
		$data = [
			'id' => (int)$payment->id,
			'id_user' => $payment->id_user,
			'date' => $payment->date,
			'type' => $payment->type,
			'payment_system' => $payment->payment_system,
			'amount' => $payment->amount,
			'currency' => $payment->currency,
			'prefix' => CurrencyRepository::getAvailableCurrencies()[strtolower($payment->currency)]['prefix'],
			'fee' => $payment->fee,
			'confirm' => $payment->confirm,
			'user_confirm' => $payment->user_confirm,
			'date_confirm' => $payment->date_confirm,
			'payer' => $payment->payer,
			'payee' => $payment->payee
		];

		return $data;
	}


	/**
	 * @param Payment $payment
	 * @return \League\Fractal\Resource\Item|null
	 */
	public function includeUser(Payment $payment): ?Item
	{
		if ($payment->id_user > 0 && !empty($payment->id_user) && User::query()->where('id', $payment->id_user)->first())
		{
			return $this->item($payment->user, new UserTransformer(), 'user');
		}

		return null;
	}
	

	/**
	 * @param Payment $payment
	 * @return \League\Fractal\Resource\Item|null
	 */
	public function includePaymentSystem(Payment $payment): ?Item
	{
		if ($payment->paymentSystem)
		{
			return $this->item($payment->paymentSystem, new PaymentSystemTransformer(), 'paymentSystem');
		}
		
		return null;
	}
}