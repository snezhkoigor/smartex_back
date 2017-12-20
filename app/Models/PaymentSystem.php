<?php

namespace App\Models;

use App\Repositories\PaymentSystemRepository;
use App\Services\MerchantWebService;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $logo
 * @property string $code
 * @property boolean $active
 * @property boolean $is_account_multi_line
 * @property boolean $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Wallet[] $wallets
 *
 * Class PaymentAccount
 * @package App\Models
 */
class PaymentSystem extends Model
{
	const CURRENCY_USD = 'usd';
	const CURRENCY_USD_PREFIX = '$';

	const CURRENCY_EUR = 'eur';
	const CURRENCY_EUR_PREFIX = '€';

	const CURRENCY_CZK = 'czk';
	const CURRENCY_CZK_PREFIX = 'Kč';

	const CURRENCY_BTC = 'btc';
	const CURRENCY_BTC_PREFIX = 'btc';

	const CURRENCY_ETH = 'eth';
	const CURRENCY_ETH_PREFIX = 'eth';

	const CURRENCY_RUB = 'rub';
	const CURRENCY_RUB_PREFIX = '₽';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'code'
	];

	protected $guarded = [
		'logo',
		'active',
		'is_account_multi_line',
		'is_deleted'
	];

	protected $dates = [
		'created_at',
		'updated_at'
	];

	public function wallets()
	{
		return $this->hasMany(Wallet::class);
	}

	public static function walletRulesById($payment_system_id)
	{
		$result = [
			'currency' => 'required|in:' . implode(',', array_keys(PaymentSystemRepository::getAvailableCurrencies())),
			'balance' => 'required|numeric',
			'account' => 'required',
			'payment_system_id' => 'required',
		];
		$fields = PaymentSystemRepository::getRequireFields();

		foreach ($fields[$payment_system_id] as $field) {
			switch ($field) {
				case 'user':
					$result[$field] = 'required';
					break;

				case 'secret':
					$result[$field] = 'required';
					break;

				case 'password':
					$result[$field] = 'required';
					break;

				case 'adv_sci':
					$result[$field] = 'required';
					break;

				case 'id_payee':
					$result[$field] = 'required';
					break;
			}
		}

		return $result;
	}
}
