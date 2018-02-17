<?php

namespace App\Models;

use Delatbabel\Elocrypt\Elocrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 *
 * @property integer $id
 * @property string $name
 * @property integer $payment_system_id
 * @property string $ps_type
 * @property string $currency
 * @property string $account
 * @property string $user
 * @property string $password
 * @property string $secret
 * @property string $adv_sci
 * @property string $id_payee
 * @property double $balance
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 *
 * @method static Wallet|QueryBuilder|EloquentBuilder query()
 * @property PaymentSystem $paymentSystem
 * @property Commission $commissions
 *
 * Class Wallet
 * @package App\Models
 */
class Wallet extends Model
{
	use Elocrypt, LogsActivity;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'currency',
		'payment_system_id',
		'adv_sci',
		'id_payee',
		'account',
		'balance',
		'user',
		'secret',
		'password',
	];

	protected $table = 'payment_account';

	protected $guarded = [
		'active',
		'ps_type',
	];

	/**
	 * The attributes that should be encrypted on save.
	 *
	 * @var array
	 */
	protected $encrypts = [
//		'user',
//		'secret',
//		'password',
	];

	protected $hidden = [
		'user',
		'secret',
		'password',
		'adv_sci',
		'id_payee',
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
		'currency',
		'payment_system_id',
		'adv_sci',
		'id_payee',
		'account',
		'balance',
		'user',
		'secret',
		'password',
		'active',
		'ps_type'
	];

	protected static $logOnlyDirty = true;

	public function getDescriptionForEvent($eventName)
	{
		return 'This wallet "' . $this->account . '" has been ' . $eventName;
	}

	public function getLogNameToUse($eventName = '')
	{
		return $eventName;
	}

	public function commissions()
	{
		return $this->hasMany(Commission::class);
	}

	public function paymentSystem()
	{
		return $this->belongsTo(PaymentSystem::class);
	}

	public static function getIn($ps_code, $currency)
	{
		return Payment::query()
			->select(DB::raw('SUM(amount) as income'))
			->where('payment_system', $ps_code)
			->where('currency', strtoupper($currency))
			->where('confirm', 1)
			->where('type', 1)
			->first();
	}

	public static function getOut($ps_code, $currency)
	{
		return Payment::query()
			->select(DB::raw('SUM(amount) as outcome'))
			->where('payment_system', $ps_code)
			->where('currency', strtoupper($currency))
			->where('confirm', 1)
			->where('type', 2)
			->first();
	}
	
	public static function getFees($ps_code, $currency)
	{
		return Payment::query()
			->select(DB::raw('SUM(fee) as fee'))
			->where('payment_system', $ps_code)
			->where('currency', strtoupper($currency))
			->where('confirm', 1)
			->whereIn('type', [1, 2])
			->first();
	}
}
