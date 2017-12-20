<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Dictionary
 * @package App\Models
 */
class Dictionary extends Model
{
	const CURRENCY_USD = 'usd';
	const CURRENCY_USD_PREFIX = '$';

	const CURRENCY_EUR = 'eur';
	const CURRENCY_EUR_PREFIX = '€';

	const CURRENCY_CZK = 'czk';
	const CURRENCY_CZK_PREFIX = 'Kc';

	const CURRENCY_BTC = 'btc';
	const CURRENCY_BTC_PREFIX = 'btc';

	const CURRENCY_ETH = 'eth';
	const CURRENCY_ETH_PREFIX = 'eth';

	protected $table = null;

	public static function getCurrencies()
	{
		return [
			self::CURRENCY_BTC => self::CURRENCY_BTC_PREFIX,
			self::CURRENCY_USD => self::CURRENCY_USD_PREFIX,
			self::CURRENCY_EUR => self::CURRENCY_EUR_PREFIX,
			self::CURRENCY_ETH => self::CURRENCY_ETH_PREFIX,
			self::CURRENCY_CZK => self::CURRENCY_CZK_PREFIX,
		];
	}
}
