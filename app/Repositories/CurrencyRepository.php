<?php

namespace App\Repositories;

use App\Models\Currency;

class CurrencyRepository
{
	/**
	 * @return array
	 */
	public static function getAvailableCurrencies()
	{
		return [
			Currency::CURRENCY_USD => [
				'name' => Currency::CURRENCY_USD,
				'prefix' => Currency::CURRENCY_USD_PREFIX
			],
			Currency::CURRENCY_EUR => [
				'name' => Currency::CURRENCY_EUR,
				'prefix' => Currency::CURRENCY_EUR_PREFIX
			],
			Currency::CURRENCY_RUB => [
				'name' => Currency::CURRENCY_RUB,
				'prefix' => Currency::CURRENCY_RUB_PREFIX
			],
			Currency::CURRENCY_CZK => [
				'name' => Currency::CURRENCY_CZK,
				'prefix' => Currency::CURRENCY_CZK_PREFIX
			],
			Currency::CURRENCY_ETH => [
				'name' => Currency::CURRENCY_ETH,
				'prefix' => Currency::CURRENCY_ETH_PREFIX
			],
			Currency::CURRENCY_BTC => [
				'name' => Currency::CURRENCY_BTC,
				'prefix' => Currency::CURRENCY_BTC_PREFIX
			],
		];
	}
}