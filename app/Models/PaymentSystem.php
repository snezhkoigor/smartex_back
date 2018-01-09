<?php

namespace App\Models;

use App\Repositories\CurrencyRepository;
use App\Repositories\PaymentSystemRepository;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property integer $id
 * @property string $name
 * @property string $logo
 * @property string $code
 * @property string $fields
 * @property boolean $active
 * @property boolean $is_account_multi_line
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
	use LogsActivity;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'code',
		'fields'
	];

	protected $guarded = [
		'logo',
		'active',
		'is_account_multi_line'
	];

	protected $dates = [
		'created_at',
		'updated_at'
	];

	protected static $ignoreChangedAttributes = [
		'updated_at'
	];

	protected static $logAttributes = [
		'name',
		'code',
		'fields',
		'logo',
		'active',
		'is_account_multi_line'
	];

	protected static $logOnlyDirty = true;

	public function getDescriptionForEvent($eventName)
	{
		return 'This payment system "' . $this->name . '" has been ' . $eventName;
	}

	public function getLogNameToUse($eventName = '')
	{
		return $eventName;
	}

	public function wallets()
	{
		return $this->hasMany(Wallet::class);
	}

	public static function walletRulesById($payment_system_id = null)
	{
		$result = [
			'currency' => 'required|in:' . implode(',', array_keys(CurrencyRepository::getAvailableCurrencies())),
			'balance' => 'nullable|numeric',
			'account' => 'required',
			'payment_system_id' => 'required',
		];
		$fields = PaymentSystemRepository::getRequireFields();

		if ($payment_system_id)
		{
			foreach ($fields[$payment_system_id] as $field)
			{
				switch ($field)
				{
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
		}

		return $result;
	}
}
