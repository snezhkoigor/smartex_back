<?php

namespace App\Repositories;

use App\Models\Currency;

class CurrencyRepository
{
	/**
	 * @return array
	 */
	public static function getAvailableCurrencies(): array
	{
		return [
			Currency::CURRENCY_USD => [
				'name' => Currency::CURRENCY_USD,
				'prefix' => Currency::CURRENCY_USD_PREFIX,
				'label' => Currency::CURRENCY_USD_PREFIX,
				'value' => Currency::CURRENCY_USD
			],
			Currency::CURRENCY_EUR => [
				'name' => Currency::CURRENCY_EUR,
				'prefix' => Currency::CURRENCY_EUR_PREFIX,
				'label' => Currency::CURRENCY_EUR_PREFIX,
				'value' => Currency::CURRENCY_EUR
			],
			Currency::CURRENCY_RUB => [
				'name' => Currency::CURRENCY_RUB,
				'prefix' => Currency::CURRENCY_RUB_PREFIX,
				'label' => Currency::CURRENCY_RUB_PREFIX,
				'value' => Currency::CURRENCY_RUB
			],
			Currency::CURRENCY_CZK => [
				'name' => Currency::CURRENCY_CZK,
				'prefix' => Currency::CURRENCY_CZK_PREFIX,
				'label' => Currency::CURRENCY_CZK_PREFIX,
				'value' => Currency::CURRENCY_CZK
			],
			Currency::CURRENCY_ETH => [
				'name' => Currency::CURRENCY_ETH,
				'prefix' => Currency::CURRENCY_ETH_PREFIX,
				'label' => Currency::CURRENCY_ETH_PREFIX,
				'value' => Currency::CURRENCY_ETH
			],
			Currency::CURRENCY_BTC => [
				'name' => Currency::CURRENCY_BTC,
				'prefix' => Currency::CURRENCY_BTC_PREFIX,
				'label' => Currency::CURRENCY_BTC_PREFIX,
				'value' => Currency::CURRENCY_BTC
			],
		];
	}
}