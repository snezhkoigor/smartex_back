<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 *
 * @property integer $id
 * @property integer $wallet_id
 * @property integer $payment_system_id
 * @property string $currency
 * @property double $commission
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PaymentSystem $paymentSystem
 * @property Wallet $wallet
 *
 * Class Commission
 * @package App
 */
class Commission extends Model
{
	use LogsActivity;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'wallet_id',
		'payment_system_id',
		'currency',
		'commission',
	];

	protected $guarded = [
		'active',
		'ps_in_type',
		'ps_out_type',
		'ps_in_currency',
		'ps_out_currency',
	];

	protected $dates = [
		'created_at',
		'updated_at'
	];

	protected $table = 'ps_commission';

	protected static $ignoreChangedAttributes = [
		'updated_at'
	];

	protected static $logAttributes = [
		'wallet_id',
		'payment_system_id',
		'currency',
		'commission',
		'active',
		'ps_in_type',
		'ps_out_type',
		'ps_in_currency',
		'ps_out_currency',
	];

	protected static $logOnlyDirty = true;

	public function getDescriptionForEvent($eventName)
	{
		return 'This commission "from ' . $this->wallet->paymentSystem->name . ', ' . $this->wallet->currency . ' (' . $this->wallet->account . ')' . ' to ' . $this->paymentSystem->name . ', ' . $this->currency . ', ' . $this->commission . '" has been ' . $eventName;
	}

	public function getLogNameToUse($eventName = '')
	{
		return $eventName;
	}

	public function paymentSystem()
	{
		return $this->belongsTo(PaymentSystem::class);
	}

	public function wallet()
	{
		return $this->belongsTo(Wallet::class);
	}
}
