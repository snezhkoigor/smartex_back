<?php

namespace App\Transformers;

use App\Models\Exchange;
use App\Repositories\CurrencyRepository;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Item;

/**
 * Class ExchangeTransformer
 * @package App\Transformers
 */
class ExchangeTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'inPayment',
		'outPayment'
	];


	/**
	 * @param Exchange $exchange
	 * @return array
	 */
	public function transform(Exchange $exchange): array
	{
		$data = [
			'id' => (int)$exchange->id,
			'date' => $exchange->date,
			'in_id_pay' => (int)$exchange->in_id_pay,
			'in_currency' => $exchange->in_currency,
			'in_prefix' => CurrencyRepository::getAvailableCurrencies()[strtolower($exchange->in_currency)]['prefix'],
			'in_amount' => (float)$exchange->in_amount,
			'in_payment' => $exchange->in_payment,
			'in_fee' => (float)$exchange->in_fee,
			'in_payee' => $exchange->in_payee,
			'comment' => $exchange->comment,
			'out_id_pay' => (int)$exchange->out_id_pay,
			'out_currency' => $exchange->out_currency,
			'out_prefix' => CurrencyRepository::getAvailableCurrencies()[strtolower($exchange->out_currency)]['prefix'],
			'out_amount' => (float)$exchange->out_amount,
			'out_fee' => (float)$exchange->out_fee,
			'out_payee' => $exchange->out_payee,
			'out_date' => $exchange->out_date,
			'out_payment' => $exchange->out_payment,
		];

		$data['in_currency_amount'] = $data['in_prefix'] . $data['in_amount'];
		$data['out_currency_amount'] = $data['out_prefix'] . $data['out_amount'];

		return $data;
	}


	/**
	 * @param Exchange $exchange
	 * @return Item|null
	 */
	public function includeInPayment(Exchange $exchange)
	{
		if ($exchange->in_id_pay)
		{
			return $this->item($exchange->inPayment, new PaymentTransformer(), 'inPayment');
		}

		return null;
	}
	
	
	/**
	 * @param Exchange $exchange
	 * @return Item|null
	 */
	public function includeOutPayment(Exchange $exchange)
	{
		if ($exchange->out_id_pay)
		{
			return $this->item($exchange->outPayment, new PaymentTransformer(), 'outPayment');
		}

		return null;
	}
}