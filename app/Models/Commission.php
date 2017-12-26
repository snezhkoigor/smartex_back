<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property integer $id
 * @property integer $wallet_id
 * @property integer $payment_system_id
 * @property string $currency
 * @property double $commission
 * @property boolean $active
 * @property boolean $is_deleted
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
		'created_at',
		'updated_at'
	];

	protected $guarded = [
		'active',
		'is_deleted',

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

	public function paymentSystem()
	{
		return $this->belongsTo(PaymentSystem::class);
	}

	public function wallet()
	{
		return $this->belongsTo(Wallet::class);
	}
}
